<?php

namespace STS\Metrics\Adapters;

use InfluxDB\Database;
use InfluxDB2\QueryApi;
use InfluxDB2\UdpWriter;
use InfluxDB2\WriteApi;
use STS\Metrics\Traits\ComputesNanosecondTimestamps;

abstract class AbstractInfluxDBAdapter
{
    use ComputesNanosecondTimestamps;

    protected Database|QueryApi $readConnection;

    protected Database|WriteApi|UdpWriter $writeConnection;

    public function getReadConnection(): Database|QueryApi
    {
        return $this->readConnection;
    }

    public function setReadConnection(Database|QueryApi $connection): static
    {
        $this->readConnection = $connection;

        return $this;
    }

    public function getWriteConnection(): Database|WriteApi|UdpWriter
    {
        return $this->writeConnection;
    }

    public function setWriteConnection(Database|WriteApi|UdpWriter $connection): static
    {
        $this->writeConnection = $connection;

        return $this;
    }

    /**
     * Pass through to the Influx client anything we don't handle.
     */
    public function __call($method, $parameters): mixed
    {
        if (strpos($method, 'write') === 0) {
            return $this->getWriteConnection()->$method(...$parameters);
        }

        return $this->getReadConnection()->$method(...$parameters);
    }

    abstract public function point(
        string $measurement,
        mixed  $value = null,
        array  $tags = [],
        array  $fields = [],
        mixed  $timestamp = null
    );

    abstract public function writePoints(array $points, $precision = null);
}