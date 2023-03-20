<?php

namespace STS\Metrics\Drivers;

use InfluxDB\Database;
use InfluxDB\Point;
use STS\Metrics\Metric;
use STS\Metrics\Traits\ComputesNanosecondTimestamps;

/**
 * Class InfluxDB
 * @package STS\Metrics\Drivers
 */
class InfluxDB extends AbstractDriver
{
    use ComputesNanosecondTimestamps;

    /**
     * @var Database
     */
    protected $readConnection;
    /**
     * @var Database
     */
    protected $writeConnection;
    /**
     * @var array
     */
    protected $points = [];
    /**
     * @var Database
     */
    protected $tcpConnection;
    /**
     * @var Database
     */
    protected $udpConnection;

    /**
     * InfluxDB constructor.
     *
     * @param $tcpConnection
     * @param $udpConnection
     */
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
     * @param mixed  $value       measurement value ... 15
     * @param array  $tags        measurement tags  ... ['host' => 'server01', 'region' => 'us-west']
     * @param array  $fields      measurement fields ... ['cpucount' => 10, 'free' => 2]
     * @param mixed  $timestamp   timestamp in nanoseconds on Linux ONLY
     *
     * @return $this
     */
    public function measurement($measurement, $value = null, array $tags = [], array $fields = [], $timestamp = null)
    {
        return $this->point(new Point(
            $measurement,
            $value,
            array_merge($this->tags, $tags),
            array_merge($this->extra, $fields),
            $this->getNanoSecondTimestamp($timestamp)
        ));
    }

    /**
     * Queue up a new point
     *
     * @param Point $point
     *
     * @return $this
     */
    public function point(Point $point)
    {
        $this->points[] = $point;

        return $this;
    }

    /**
     * @return $this
     * @throws \InfluxDB\Exception
     */
    public function flush()
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
     * @param Metric $metric
     *
     * @return Point
     * @throws \InfluxDB\Database\Exception
     */
    public function format(Metric $metric)
    {
        return new Point(
            $metric->getName(),
            $metric->getValue(),
            array_merge($this->tags, $metric->getTags()),
            array_merge($this->extra, $metric->getExtra()),
            $this->getNanoSecondTimestamp($metric->getTimestamp())
        );
    }

    /**
     * Send one or more metrics to InfluxDB now
     *
     * @param $metrics
     *
     * @throws \InfluxDB\Exception
     */
    public function send($metrics)
    {
        $this->getWriteConnection()->writePoints(
            array_map(function ($metric) {
                return $this->format($metric);
            }, (array)$metrics)
        );
    }

    /**
     * @return array
     */
    public function getPoints()
    {
        return $this->points;
    }

    /**
     * @return Database
     */
    public function getWriteConnection()
    {
        return $this->writeConnection;
    }

    /**
     * @param Database $connection
     */
    public function setWriteConnection(Database $connection)
    {
        $this->writeConnection = $connection;
    }

    /**
     * @return Database
     */
    public function getReadConnection()
    {
        return $this->readConnection;
    }

    /**
     * @param Database $connection
     */
    public function setReadConnection(Database $connection)
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
    public function __call($method, $parameters)
    {
        if (strpos($method, 'write') === 0) {
            return $this->getWriteConnection()->$method(...$parameters);
        }

        return $this->getReadConnection()->$method(...$parameters);
    }
}
