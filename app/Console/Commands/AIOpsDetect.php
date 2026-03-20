<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\PrometheusClient;

class AIOpsDetect extends Command
{
    protected $signature = 'aiops:detect';
    protected $description = 'Run AIOps Detection Engine';

    private $baseline = [
        'latency' => null,
        'error_rate' => null,
        'traffic' => null
    ];

    public function handle()
    {
        $this->info("AIOps Detection Engine started...");

        $client = new PrometheusClient();

        while (true) {

            $this->info("Checking metrics...");

            $metrics = $client->getBasicMetrics();

            $latency = $this->extractValue($metrics['latency_p95']);
            $errorRate = $this->extractValue($metrics['error_rate']);
            $traffic = $this->extractValue($metrics['request_rate']);

            // ===== BASELINE UPDATE =====
            $this->updateBaseline('latency', $latency);
            $this->updateBaseline('error_rate', $errorRate);
            $this->updateBaseline('traffic', $traffic);

            // ===== PRINT =====
            $this->info("Latency: $latency | Baseline: " . $this->baseline['latency']);
            $this->info("Error Rate: $errorRate | Baseline: " . $this->baseline['error_rate']);
            $this->info("Traffic: $traffic | Baseline: " . $this->baseline['traffic']);

            // ===== DETECTION =====
            $this->detectLatency($latency);
            $this->detectError($errorRate);
            $this->detectTraffic($traffic);

            sleep(20);
        }
    }

    // =========================
    // BASELINE (Moving Average)
    // =========================
    private function updateBaseline($key, $value)
    {
        if ($value == 0) return;

        if ($this->baseline[$key] === null) {
            $this->baseline[$key] = $value;
        } else {
            $this->baseline[$key] = ($this->baseline[$key] + $value) / 2;
        }
    }

    // =========================
    // DETECTION RULES
    // =========================

    private function detectLatency($latency)
    {
        if ($this->baseline['latency'] && $latency > 3 * $this->baseline['latency']) {

            $incident = $this->createIncident(
                "LATENCY_SPIKE",
                "HIGH",
                $this->baseline['latency'],
                $latency
            );

            $this->sendAlert($incident);
        }
    }

    private function detectError($errorRate)
    {
        if ($errorRate > 0.1) { // 10%

            $incident = $this->createIncident(
                "ERROR_STORM",
                "CRITICAL",
                $this->baseline['error_rate'],
                $errorRate
            );

            $this->sendAlert($incident);
        }
    }

    private function detectTraffic($traffic)
    {
        if ($this->baseline['traffic'] && $traffic > 2 * $this->baseline['traffic']) {

            $incident = $this->createIncident(
                "TRAFFIC_SURGE",
                "MEDIUM",
                $this->baseline['traffic'],
                $traffic
            );

            $this->sendAlert($incident);
        }
    }

    // =========================
    // INCIDENT CREATION
    // =========================

    private function createIncident($type, $severity, $baseline, $observed)
    {
        $incident = [
            "incident_id" => uniqid(),
            "incident_type" => $type,
            "severity" => $severity,
            "status" => "OPEN",
            "detected_at" => now()->toISOString(),
            "affected_service" => "laravel-api",
            "affected_endpoints" => ["api"],
            "triggering_signals" => [$type],
            "baseline_values" => $baseline,
            "observed_values" => $observed,
            "summary" => "$type detected"
        ];

        $path = storage_path("aiops/incidents.json");

        if (!file_exists(dirname($path))) {
            mkdir(dirname($path), 0777, true);
        }

        file_put_contents(
            $path,
            json_encode($incident, JSON_PRETTY_PRINT) . PHP_EOL,
            FILE_APPEND
        );

        return $incident;
    }

    // =========================
    // ALERT SYSTEM
    // =========================

    private function sendAlert($incident)
    {
        $this->warn("🚨 INCIDENT DETECTED!");
        $this->line(json_encode($incident, JSON_PRETTY_PRINT));
    }

    // =========================
    // HELPER
    // =========================

    private function extractValue($metric)
    {
        return isset($metric['data']['result'][0]['value'][1])
            ? (float)$metric['data']['result'][0]['value'][1]
            : 0;
    }
}