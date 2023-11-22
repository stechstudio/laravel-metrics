<?php

namespace STS\Metrics\Drivers;

use Prometheus\Collector;
use Prometheus\CollectorRegistry;
use Prometheus\Counter;
use Prometheus\Exception\MetricNotFoundException;
use Prometheus\Exception\MetricsRegistrationException;
use Prometheus\Gauge;
use Prometheus\RendererInterface;
use STS\Metrics\Metric;
use STS\Metrics\MetricType;

/**
 * The idea of this driver is to use Prometheus\CollectorRegistry only to format the already collected metrics in prometheus format.
 */
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
            MetricType::COUNTER => (function () use ($metric) {
                $counter = $this->registry->getOrRegisterCounter($metric->getNamespace(), $metric->getName(), $metric->getDescription() ?? '', array_keys($metric->getTags()));
                $counter->incBy($metric->getValue(), array_values($metric->getTags()));
                return $counter;
            })(),
            MetricType::GAUGE => (function () use ($metric) {
                $gauge = $this->registry->getOrRegisterGauge($metric->getNamespace(), $metric->getName(), $metric->getDescription() ?? '', array_keys($metric->getTags()));
                $gauge->set($metric->getValue(), array_values($metric->getTags()));
                return $gauge;
            })(),
            default => throw new \UnhandledMatchError($metric->getType()),
        };
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
        // Always before formatting all metrics we need to wipe the registry storage and to register the metrics again.
        // If we don't, we will increment existing counters instead of replacing them.
        $this->registry->wipeStorage();

        collect($this->getMetrics())->each(function (Metric $metric) {
            $this->format($metric);
        });

        return $this->renderer->render($this->registry->getMetricFamilySamples());
    }
}
