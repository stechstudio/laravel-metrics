<?php

namespace STS\Metrics\Drivers;

use Prometheus\MetricFamilySamples;
use Prometheus\RenderTextFormat;
use STS\Metrics\Metric;

class PrometheusDriver extends AbstractDriver
{
    public function format(Metric $metric): array
    {
        $renderer = new RenderTextFormat();
        return [$renderer->render([new MetricFamilySamples([
            'name' => $metric->getName(),
            // TODO: Fix these hardcoded values
            'type' => 'type',
            'help' => 'help',
            'labelNames' => [],
            'samples' => [[
                'name' => $metric->getName(),
                'labelNames' => collect($metric->getTags())->keys()->toArray(),
                'labelValues' => collect($metric->getTags())->values()->toArray(),
                'value' => $metric->getValue(),
            ]],
        ])])];
    }

    public function flush(): static
    {
        return $this;
    }
}
