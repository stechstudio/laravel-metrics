<?php

namespace STS\Metrics\Drivers;

use InfluxDB\Database;
use InfluxDB\Exception;
use InfluxDB\Point AS IDBPoint;
use InfluxDB2\Point AS IDB2Point;
use InfluxDB2\UdpWriter;
use InfluxDB2\WriteApi;
use STS\Metrics\Adapters\AbstractInfluxDBAdapter;
use STS\Metrics\Metric;

class InfluxDB extends AbstractDriver
{
    protected array $points = [];

    protected AbstractInfluxDBAdapter $adapter;

    public function __construct(AbstractInfluxDBAdapter $adapter)
    {
        $this->adapter = $adapter;
    }

    public function measurement(
        string $measurement,
        mixed  $value = null,
        array  $tags = [],
        array  $fields = [],
        mixed  $timestamp = null
    ): static
    {
        return $this->point(
            $this->adapter->point(
                $measurement,
                $value,
                array_merge($this->tags, $tags),
                array_merge($this->getExtra(), $fields),
                $timestamp
            )
        );
    }

    public function point(IDBPoint|IDB2Point $point): static
    {
        $this->points[] = $point;

        return $this;
    }

    /**
     * @throws Exception
     */
    public function flush(): static
    {
        if (empty($this->getMetrics())) {
            return $this;
        }

        $this->flushMetricLogs();

        $this->send($this->getMetrics());
        $this->metrics = [];

        if (count($this->points)) {
            $this->adapter->writePoints($this->points);
            $this->points = [];
        }

        return $this;
    }

    /**
     * @throws Database\Exception
     */
    public function format(Metric $metric): IDBPoint|IDB2Point
    {
        return $this->adapter->point(
            $metric->getName(),
            $metric->getValue() ?? 1,
            array_merge($this->tags, $metric->getTags()),
            array_merge($this->getExtra(), $metric->getExtra()),
            $metric->getTimestamp()
        );
    }

    /**
     * @throws Exception
     * @throws Database\Exception
     */
    public function send($metrics): void
    {
        $this->adapter->writePoints(
            array_map(function ($metric) {
                return $this->format($metric);
            }, (array)$metrics)
        );
    }

    public function getPoints(): array
    {
        return $this->points;
    }

    public function getWriteConnection(): Database|WriteApi|UdpWriter
    {
        return $this->adapter->getWriteConnection();
    }

    /**
     * Pass through to the Influx adapter anything we don't handle.
     *
     * @param $method
     * @param $parameters
     *
     * @return mixed
     */
    public function __call($method, $parameters): mixed
    {
        return $this->adapter->$method(...$parameters);
    }
}
