<?php

namespace STS\Metrics\Adapters;

use InfluxDB\Database;
use InfluxDB\Database\Exception;
use InfluxDB\Point;

class InfluxDB1Adapter extends AbstractInfluxDBAdapter
{
    public function __construct(Database $tcpConnection, ?Database $udpConnection = null)
    {
        $this->readConnection = $tcpConnection;

        $this->writeConnection = is_null($udpConnection)
            ? $tcpConnection
            : $udpConnection;
    }

    /**
     * @throws Exception
     */
    public function point(
        string $measurement,
        mixed  $value = null,
        array  $tags = [],
        array  $fields = [],
        mixed  $timestamp = null
    ): Point
    {
        return new Point(
            $measurement,
            $value,
            $tags,
            $fields,
            $this->getNanoSecondTimestamp($timestamp)
        );
    }

    /**
     * @throws \InfluxDB\Exception
     */
    public function writePoints(array $points, $precision = Database::PRECISION_NANOSECONDS)
    {
        return $this->getWriteConnection()->writePoints($points, $precision);
    }

}