<?php

namespace STS\Metrics\Drivers;

use Prometheus\Collector;
use Prometheus\CollectorRegistry;
use Prometheus\Counter;
use Prometheus\Exception\MetricNotFoundException;
use Prometheus\Exception\MetricsRegistrationException;
use Prometheus\RendererInterface;
use STS\Metrics\Metric;
use STS\Metrics\MetricType;

class PrometheusDriver extends AbstractDriver
{
    public function __construct(readonly private RendererInterface $renderer, readonly private CollectorRegistry $registry)
    {

    }

    /**
     * @throws MetricsRegistrationException
     */
    public function format(Metric $metric): Collector
    {
        return match ($metric->getType()) {
            MetricType::COUNTER => $this->formatCounter($metric),
            default => throw new \UnhandledMatchError($metric->getType()),
        };
    }

    /**
     * @throws MetricsRegistrationException
     */
    private function formatCounter(Metric $metric): Counter
    {
        try {
            $counter = $this->registry->getCounter($metric->getNamespace(), $metric->getName());
        } catch (MetricNotFoundException) {
            $counter = $this->registry->registerCounter($metric->getNamespace(), $metric->getName(), $metric->getName(), array_keys($metric->getTags()));
            $counter->incBy($metric->getValue(), array_values($metric->getTags()));
        }
        return $counter;
    }


    public function flush(): static
    {
        $this->metrics = [];
        $this->registry->wipeStorage();
        return $this;
    }

    /**
     * Renders all collected metrics in prometheus format.
     * The result can be directly exposed on HTTP endpoint, for polling by Prometheus.
     *
     * @return string
     * @throws MetricsRegistrationException
     */
    public function formatted(): string
    {
        collect($this->getMetrics())->each(function (Metric $metric) {
            $this->format($metric);
        });

        return $this->renderer->render($this->registry->getMetricFamilySamples());
    }
}
