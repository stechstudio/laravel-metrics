<?php

namespace STS\Metrics\Drivers;

use Exception;
use STS\Metrics\Metric;
use PostHog\PostHog AS PostHogClient;

class PostHog extends AbstractDriver
{
    public function __construct(protected string $distinctPrefix = '')
    {
    }

    public function flush(): static
    {
        $this->flushMetricLogs();

        foreach ($this->getMetrics() as $metric) {
            PostHogClient::capture($this->format($metric));
        }

        $this->metrics = [];

        PostHogClient::flush();

        return $this;
    }

    public function format(Metric $metric): array
    {
        return [
            'distinctId' => $this->getUserId(),
            'event' => $metric->getName(),
            'properties' => array_merge(
                $metric->getValue()
                    ? ['value' => $metric->getValue()]
                    : [],
                $this->getExtra(),
                $metric->getExtra()
            ),
        ];
    }

    public function getUserId(): mixed
    {
        if ($this->userIdResolver) {
            return call_user_func($this->userIdResolver, $this);
        }

        return $this->distinctPrefix . parent::getUserId();
    }

    /**
     * Pass through PostHog anything we don't handle.
     *
     * @param $method
     * @param $parameters
     *
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return PostHogClient::$method(...$parameters);
    }
}
