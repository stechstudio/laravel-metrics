<?php

namespace STS\Metrics\Drivers;

use Prometheus\Collector;
use Prometheus\CollectorRegistry;
use Prometheus\RendererInterface;
use Prometheus\RenderTextFormat;
use Prometheus\Storage\InMemory;
use STS\Metrics\Metric;

class PrometheusDriver extends AbstractDriver
{
    public function __construct(protected RendererInterface $renderer, protected CollectorRegistry $registry)
    {
    }

    public function format(Metric $metric): Collector
    {
        //TODO: Handle other metric types and exception
        $counter = $this->registry->registerCounter('Test', $metric->getName(), $metric->getName(), $metric->getTags());
        $counter->incBy($metric->getValue(), $metric->getTags());

        return $counter;
    }

    public function flush(): static
    {
        if (empty($this->getMetrics())) {
            return $this;
        }

        collect($this->getMetrics())->each(function (Metric $metric) {
            $this->format($metric);
        });
        $result = $this->renderer->render($this->registry->getMetricFamilySamples());
        $this->metrics = [];

        $this->registry->wipeStorage();

        echo $result;
        return $this;
    }
}
