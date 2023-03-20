<?php

namespace STS\Metrics\Adapters;

use STS\Metrics\Traits\ComputesNanosecondTimestamps;

abstract class AbstractInfluxDBAdapter
{
    use ComputesNanosecondTimestamps;

    /**
     * @var \InfluxDB\Database|\InfluxDB2\QueryApi
     */
    protected $readConnection;
    /**
     * @var \InfluxDB\Database|\InfluxDB2\WriteApi|\InfluxDB2\UdpWriter
     */
    protected $writeConnection;

    /**
     * @return \InfluxDB\Database|\InfluxDB2\QueryApi
     */
    public function getReadConnection()
    {
        return $this->readConnection;
    }

    /**
     * @param \InfluxDB\Database|\InfluxDB2\QueryApi $connection
     * @return void
     */
    public function setReadConnection($connection)
    {
        $this->readConnection = $connection;
    }

    /**
     * @return \InfluxDB\Database|\InfluxDB2\WriteApi|\InfluxDB2\UdpWriter
     */
    public function getWriteConnection()
    {
        return $this->writeConnection;
    }

    /**
     * @param \InfluxDB\Database|\InfluxDB2\WriteApi|\InfluxDB2\UdpWriter $connection
     * @return void
     */
    public function setWriteConnection($connection)
    {
        $this->writeConnection = $connection;
    }

    /**
     * Pass through to the Influx client anything we don't handle.
     *
     * @param $method
     * @param $parameters
     * @return void
     */
    public function __call($method, $parameters)
    {
        if (strpos($method, 'write') === 0) {
            return $this->getWriteConnection()->$method(...$parameters);
        }

        return $this->getReadConnection()->$method(...$parameters);
    }

    /**
     * @param string $measurement
     * @param float $value
     * @param array $tags
     * @param array $additionalFields
     * @param int $timestamp
     * @return \InfluxDB\Point|\InfluxDB2\Point
     */
    abstract public function point(
        $measurement,
        $value = null,
        $tags = [],
        $additionalFields = [],
        $timestamp = null
    );

    /**
     * @param array $points
     * @param string $precision
     * @return bool
     */
    abstract public function writePoints($points, $precision);
}