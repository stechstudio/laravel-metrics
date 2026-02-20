<?php

namespace STS\Metrics\Drivers;

use Aws\CloudWatch\CloudWatchClient;
use STS\Metrics\Metric;

class CloudWatch extends AbstractDriver
{
    protected CloudWatchClient $client;

    protected string $namespace;

    public function __construct(CloudWatchClient $client, $namespace)
    {
        $this->setClient($client);
        $this->namespace = $namespace;
    }

    public function getClient(): CloudWatchClient
    {
        return $this->client;
    }

    public function setClient(CloudWatchClient $client): void
    {
        $this->client = $client;
    }

    public function flush(): static
    {
        if (!count($this->getMetrics())) {
            return $this;
        }

        $this->flushMetricLogs();

        $this->send($this->getMetrics());

        $this->metrics = [];

        return $this;
    }

    public function send($metrics): void
    {
        $this->getClient()->putMetricData([
            'MetricData' => array_map(function ($metric) {
                return $this->format($metric);
            }, (array)$metrics),
            'Namespace'  => $this->namespace
        ]);
    }

    public function format(Metric $metric): array
    {
        return array_merge(
            array_filter([
                'MetricName'        => $metric->getName(),
                'Dimensions'        => $this->formatDimensions(array_merge($this->tags, $metric->getTags())),
                'StorageResolution' => in_array($metric->getResolution(), [1, 60]) ? $metric->getResolution() : null,
                'Timestamp'         => $this->formatTimestamp($metric->getTimestamp()),
                'Unit'              => $metric->getUnit()
            ]),
            $metric->getValue() === null
                ? []
                : ['Value' => $metric->getValue()]
        );
    }

    protected function formatTimestamp($timestamp): int
    {
        if (is_numeric($timestamp) && strlen($timestamp) === 10) {
            // This appears to be in seconds already
            return $timestamp;
        }

        if ($timestamp instanceof \DateTime) {
            return $timestamp->getTimestamp();
        }

        if (preg_match("/\d{10}\.\d{4}/", $timestamp)) {
            // This looks like a microtime float
            return (int)$timestamp;
        }

        // I don't know what you have, just going to generate a new timestamp
        return time();
    }

    protected function formatDimensions(array $dimensions): array
    {
        return array_map(function ($key, $value) {
            return [
                'Name' => $key,
                'Value' => $value
            ];
        }, array_keys($dimensions), $dimensions);
    }

    /**
     * Pass through to the CloudWatch client anything we don't handle.
     *
     * @param $method
     * @param $parameters
     *
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->getClient()->$method(...$parameters);
    }
}
