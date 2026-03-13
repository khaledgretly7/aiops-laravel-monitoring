<?php

namespace App\Http\Controllers;

use Prometheus\RenderTextFormat;

class MetricsController extends Controller
{
    public function metrics()
    {
        $registry = app('prometheus.registry');

        $renderer = new RenderTextFormat();

        return response(
            $renderer->render($registry->getMetricFamilySamples()),
            200,
            ['Content-Type' => RenderTextFormat::MIME_TYPE]
        );
    }
}