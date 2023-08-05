<?php

namespace STS\Metrics\Drivers;

use InfluxDB\Database;
use InfluxDB\Exception;
use InfluxDB\Point;
use STS\Metrics\Metric;

class InfluxDB extends AbstractDriver
{
    protected Database $readConnection;

    protected Database $writeConnection;

    protected array $points = [];

    protected Database $tcpConnection;

    protected Database $udpConnection;

    public function __construct($tcpConnection, $udpConnection = null)
    {
        $this->readConnection = $tcpConnection;

        $this->writeConnection = is_null($udpConnection)
            ? $tcpConnection
            : $udpConnection;
    }

    /**
     * Queue up a new measurement
     *
     * @param string $measurement the name of the measurement ... 'this-data'
     * @param mixed|null $value measurement value ... 15
     * @param array $tags measurement tags  ... ['host' => 'server01', 'region' => 'us-west']
     * @param array $fields measurement fields ... ['cpucount' => 10, 'free' => 2]
     * @param mixed|null $timestamp timestamp in nanoseconds on Linux ONLY
     *
     * @return $this
     */
    public function measurement(
        string $measurement,
        mixed  $value = null,
        array  $tags = [],
        array  $fields = [],
        mixed  $timestamp = null
    ): static
    {
        return $this->point(new Point(
            $measurement,
            $value,
            array_merge($this->tags, $tags),
            array_merge($this->extra, $fields),
            $this->getNanoSecondTimestamp($timestamp)
        ));
    }

    public function point(Point $point): static
    {
        $this->points[] = $point;

        return $this;
    }

    /**
     * A public way to get the nanosecond precision we desire.
     */
    public function getNanoSecondTimestamp(mixed $timestamp = null): int
    {
        if ($timestamp instanceof \DateTime) {
            return $timestamp->getTimestamp() * 1000000000;
        }

        if (strlen($timestamp) === 19) {
            // Looks like it is already nanosecond precise!
            return $timestamp;
        }

        if (strlen($timestamp) === 10) {
            // This appears to be in seconds
            return $timestamp * 1000000000;
        }

        if (preg_match("/\d{10}\.\d{4}/", $timestamp)) {
            // This looks like a microtime float
            return (int)($timestamp * 1000000000);
        }

        // We weren't given a valid timestamp, generate.
        return (int)(microtime(true) * 1000000000);
    }

    /**
     * @throws Exception
     */
    public function flush(): static
    {
        if (empty($this->getMetrics())) {
            return $this;
        }

        $this->send($this->getMetrics());
        $this->metrics = [];

        if (count($this->points)) {
            $this->getWriteConnection()->writePoints($this->points);
            $this->points = [];
        }

        return $this;
    }

    /**
     * @throws Database\Exception
     */
    public function format(Metric $metric): Point
    {
        return new Point(
            $metric->getName(),
            $metric->getValue() ?? 1,
            array_merge($this->tags, $metric->getTags()),
            array_merge($this->extra, $metric->getExtra()),
            $this->getNanoSecondTimestamp($metric->getTimestamp())
        );
    }

    /**
     * @throws Exception
     * @throws Database\Exception
     */
    public function send($metrics): void
    {
        $this->getWriteConnection()->writePoints(
            array_map(function ($metric) {
                return $this->format($metric);
            }, (array)$metrics)
        );
    }

    public function getPoints(): array
    {
        return $this->points;
    }

    public function getWriteConnection(): Database
    {
        return $this->writeConnection;
    }

    public function setWriteConnection(Database $connection): void
    {
        $this->writeConnection = $connection;
    }

    public function getReadConnection(): Database
    {
        return $this->readConnection;
    }

    public function setReadConnection(Database $connection): void
    {
        $this->readConnection = $connection;
    }

    /**
     * Pass through to the Influx client anything we don't handle.
     *
     * @param $method
     * @param $parameters
     *
     * @return mixed
     */
    public function __call($method, $parameters): mixed
    {
        if (str_starts_with($method, 'write')) {
            return $this->getWriteConnection()->$method(...$parameters);
        }

        return $this->getReadConnection()->$method(...$parameters);
    }
}
