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

    /**
     * Note we are NOT enqueueing metrics on our own with PostHog. It queues internally
     * and batches sends as it sees fit. Our best bet is to let PostHog do its thing.
     * @throws Exception
     */
    public function add(Metric $metric): static
    {
        PostHogClient::capture($this->format($metric));

        return $this;
    }

    /**
     * PostHog sends batches automatically on __destruct, this really isn't necessary.
     * But we're including it in case you ever want to force send earlier on.
     */
    public function flush(): static
    {
        PostHogClient::flush();

        return $this;
    }

    public function format(Metric $metric): array
    {
        return [
            'distinctId' => $this->distinctPrefix . $this->getUserId(),
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
