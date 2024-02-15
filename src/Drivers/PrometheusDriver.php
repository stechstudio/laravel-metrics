<?php

namespace STS\Metrics\Drivers;

use Illuminate\Support\Str;
use Prometheus\Collector;
use Prometheus\CollectorRegistry;
use Prometheus\Exception\MetricsRegistrationException;
use Prometheus\RendererInterface;
use STS\Metrics\Metric;
use STS\Metrics\MetricsServiceProvider;
use STS\Metrics\MetricType;
use UnhandledMatchError;

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
     * @throws UnhandledMatchError
     */
    public function format(Metric $metric): Collector
    {
        $namespace = Str::snake($metric->getNamespace());
        $name = Str::snake($metric->getName());
        $labelKeys = array_map(fn ($tag) => Str::snake($tag) , array_keys($metric->getTags()));
        return match ($metric->getType()) {
            MetricType::COUNTER => (function () use ($namespace, $name, $labelKeys, $metric) {
                $counter = $this->registry->getOrRegisterCounter($namespace, $name, $metric->getDescription() ?? '', $labelKeys);
                $counter->incBy($metric->getValue(), array_values($metric->getTags()));
                return $counter;
            })(),
            MetricType::GAUGE => (function () use ($namespace, $name, $labelKeys, $metric) {
                $gauge = $this->registry->getOrRegisterGauge($namespace, $name, $metric->getDescription() ?? '', $labelKeys);
                $gauge->set($metric->getValue(), array_values($metric->getTags()));
                return $gauge;
            })(),
            default => throw new UnhandledMatchError($metric->getType()),
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
     * If execution is thrown no matter in the context of long-running process or http request,
     * there are handlers in @see MetricsServiceProvider to call flush and clear the state
     *
     * @return string
     * @throws MetricsRegistrationException
     * @throws UnhandledMatchError
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
