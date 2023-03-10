<?php

namespace STS\Metrics\Drivers;

use STS\Metrics\Metric;
use Psr\Log\LoggerInterface;

/**
 * Class LogDriver
 * @package STS\Metrics\Drivers
 */
class LogDriver extends AbstractDriver
{

    /**
     * @var LoggerInterface
     */
    protected $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function format(Metric $metric)
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

    public function flush()
    {
        if (!count($this->getMetrics())) {
            return $this;
        }

        $formatted = array_map([$this, 'format'], $this->getMetrics());

        $this->logger->info("Metrics", $formatted);

        $this->metrics = [];

        return $this;
    }
}
