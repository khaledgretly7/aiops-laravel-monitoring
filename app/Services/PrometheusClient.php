<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class PrometheusClient
{
    private $baseUrl = "http://localhost:9090/api/v1/query";

    private function query($query)
    {
        $response = Http::get($this->baseUrl, [
            'query' => $query
        ]);

        return $response->json();
    }

    public function getBasicMetrics()
    {
        return [
            "request_rate" => $this->query('rate(aiops_http_requests_total[1m])'),
            "error_rate" => $this->query('rate(aiops_http_errors_total[1m])'),
            "latency_p95" => $this->query(
                'histogram_quantile(0.95, rate(aiops_http_request_duration_seconds_bucket[1m]))'
            )
        ];
    }
}