<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Prometheus\CollectorRegistry;
use Prometheus\Storage\APC;

class TelemetryMiddleware
{
    private static ?CollectorRegistry $registry = null;

   private function getRegistry(): CollectorRegistry
{
    if (!app()->has('prometheus.registry')) {
        app()->instance(
            'prometheus.registry',
            new CollectorRegistry(new APC())
        );
    }

    return app('prometheus.registry');
}

    public function handle(Request $request, Closure $next)
    {   
        $registry = $this->getRegistry();

        if ($request->path() === 'api/metrics') {
            return $next($request);
        }

        $start = microtime(true);

        

        $requestCounter = $registry->getOrRegisterCounter(
            'aiops',
            'http_requests_total',
            'Total HTTP requests',
            ['method','path','status']
        );

        $errorCounter = $registry->getOrRegisterCounter(
            'aiops',
            'http_errors_total',
            'Total HTTP errors',
            ['method','path','error_category']
        );

        $histogram = $registry->getOrRegisterHistogram(
            'aiops',
            'http_request_duration_seconds',
            'Request duration',
            ['method','path'],
            [0.05,0.1,0.25,0.5,1,2.5,5,10]
        );

        $correlationId = $request->header('X-Request-Id') ?? Str::uuid()->toString();

        $response = null;
        $errorCategory = null;

        try {

            $response = $next($request);

        } catch (\Illuminate\Validation\ValidationException $e) {

            $errorCategory = "VALIDATION_ERROR";
            throw $e;

        } catch (\Illuminate\Database\QueryException $e) {

            $errorCategory = "DATABASE_ERROR";
            throw $e;

        } catch (\Exception $e) {

            $errorCategory = "SYSTEM_ERROR";
            throw $e;

        } finally {

            $latency = (microtime(true) - $start) * 1000;
            $latencySeconds = $latency / 1000;

            $requestCounter->inc([
                $request->method(),
                $request->path(),
                $response?->getStatusCode() ?? "500"
            ]);

            $histogram->observe(
                $latencySeconds,
                [
                    $request->method(),
                    $request->path()
                ]
            );

            if ($latency > 4000) {
                $errorCategory = "TIMEOUT_ERROR";
            }

            if ($errorCategory) {
                $errorCounter->inc([
                    $request->method(),
                    $request->path(),
                    $errorCategory
                ]);
            }

            $log = [
                "timestamp" => now()->toISOString(),
                "correlation_id" => $correlationId,
                "method" => $request->method(),
                "path" => $request->path(),
                "status_code" => $response?->getStatusCode(),
                "latency_ms" => round($latency, 2),
                "client_ip" => $request->ip(),
                "user_agent" => $request->userAgent(),
                "query" => $request->getQueryString(),
                "payload_size_bytes" => strlen($request->getContent()),
                "response_size_bytes" => $response && $response->getContent()
                    ? strlen($response->getContent())
                    : null,
                "route_name" => optional($request->route())->getName() ?? "unknown",
                "severity" => $errorCategory ? "error" : "info",
                "build_version" => env("BUILD_VERSION"),
                "host" => gethostname(),
                "error_category" => $errorCategory
            ];

            Log::channel('aiops')->info("request_log", $log);
        }

        $response->headers->set('X-Request-Id', $correlationId);

        return $response;
    }
}