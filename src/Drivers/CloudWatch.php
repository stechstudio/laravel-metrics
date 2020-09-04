<?php

namespace STS\Metrics\Drivers;

use Aws\CloudWatch\CloudWatchClient;
use STS\Metrics\Metric;

/**
 * Class CloudWatch
 * @package STS\Metrics\Drivers
 */
class CloudWatch extends AbstractDriver
{
    /**
     * @var CloudWatchClient
     */
    protected $client;

    /**
     * @var string
     */
    protected $namespace;

    /**
     * CloudWatch constructor.
     *
     * @param CloudWatchClient $client
     * @param                  $namespace
     */
    public function __construct(CloudWatchClient $client, $namespace)
    {
        $this->setClient($client);
        $this->namespace = $namespace;
    }

    /**
     * @return CloudWatchClient
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @param CloudWatchClient $client
     */
    public function setClient(CloudWatchClient $client)
    {
        $this->client = $client;
    }

    /**
     * Flush all queued metrics to CloudWatch
     *
     * @return $this
     */
    public function flush()
    {
        if (!count($this->getMetrics())) {
            return $this;
        }

        $this->send($this->getMetrics());

        $this->metrics = [];

        return $this;
    }

    /**
     * Send one or more metrics to CloudWatch now
     *
     * @param $metrics
     */
    public function send($metrics)
    {
        $this->getClient()->putMetricData([
            'MetricData' => array_map(function ($metric) {
                return $this->format($metric);
            }, (array)$metrics),
            'Namespace'  => $this->namespace
        ]);
    }

    /**
     * @param Metric $metric
     *
     * @return array
     */
    public function format(Metric $metric)
    {
        return array_merge(
            array_filter([
                'MetricName'        => $metric->getName(),
                'Dimensions'        => $this->formatDimensions(array_merge($this->tags, $metric->getTags())),
                'StorageResolution' => in_array($metric->getResolution(), [1, 60]) ? $metric->getResolution() : null,
                'Timestamp'         => $this->formatTimestamp($metric->getTimestamp()),
                'Unit'              => $metric->getUnit()
            ]),
            [
                'Value' => $metric->getValue()
            ]);
    }

    /**
     * @param $timestamp
     *
     * @return int
     */
    protected function formatTimestamp($timestamp)
    {
        if (is_numeric($timestamp) && strlen($timestamp) == 10) {
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

    protected function formatDimensions(array $dimensions)
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
