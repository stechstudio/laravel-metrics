<?php

namespace STS\Metrics\Drivers;

use Prometheus\Collector;
use Prometheus\CollectorRegistry;
use Prometheus\RendererInterface;
use STS\Metrics\Metric;

class PrometheusDriver extends AbstractDriver
{
    protected string $rendered = '';

    public function __construct(protected RendererInterface $renderer, protected CollectorRegistry $registry)
    {

    }

    public function format(Metric $metric): Collector
    {
        //TODO: Handle other metric types via adapter and handle exceptions
        $counter = $this->registry->registerCounter($metric->getNamespace(), $metric->getName(), $metric->getName(), array_keys($metric->getTags()));
        $counter->incBy($metric->getValue(), array_values($metric->getTags()));

        return $counter;
    }

    public function flush(): static
    {
        if (empty($this->getMetrics())) {
            $this->registry->wipeStorage();
            return $this;
        }

        collect($this->getMetrics())->each(function (Metric $metric) {
            $this->format($metric);
        });

        $this->rendered = $this->renderer->render($this->registry->getMetricFamilySamples());

        $this->metrics = [];

        $this->registry->wipeStorage();
        return $this;
    }

    public function render(): string
    {
        return $this->flush()->rendered;
    }
}
