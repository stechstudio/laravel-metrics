<?php

namespace STS\Metrics\Drivers;

use STS\Metrics\Metric;
use Psr\Log\LoggerInterface;

class LogDriver extends AbstractDriver
{
    public function __construct(protected LoggerInterface $logger)
    {
    }

    public function format(Metric $metric): array
    {
        return array_filter([
            'name' => $metric->getName(),
            'value' => $metric->getValue(),
            'resolution' => $metric->getResolution(),
            'unit' => $metric->getUnit(),
            'tags' => $metric->getTags(),
            'extra' => $metric->getExtra(),
            'timestamp' => $metric->getTimestamp()
        ]);
    }

    public function flush(): static
    {
        $this->flushMetricLogs();

        if (!count($this->getMetrics())) {
            return $this;
        }

        $formatted = array_map([$this, 'format'], $this->getMetrics());

        $this->logger->info("Metrics", $formatted);

        $this->metrics = [];

        return $this;
    }
}
