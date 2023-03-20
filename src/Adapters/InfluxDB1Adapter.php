<?php

namespace STS\Metrics\Adapters;

class InfluxDB1Adapter extends AbstractInfluxDBAdapter
{

    /**
     * @param \InfluxDB\Database $tcpConnection
     * @param \InfluxDB\Database $udpConnection
     */
    public function __construct($tcpConnection, $udpConnection = null)
    {
        $this->readConnection = $tcpConnection;

        $this->writeConnection = is_null($udpConnection)
            ? $tcpConnection
            : $udpConnection;
    }


    /**
     * @inheritDoc
     */
    public function point($measurement, $value = null, $tags = [], $additionalFields = [], $timestamp = null)
    {
        return new \InfluxDB\Point(
            $measurement,
            $value,
            $tags,
            $additionalFields,
            $this->getNanoSecondTimestamp($timestamp)
        );
    }

    /**
     * @inheritDoc
     */
    public function writePoints($points, $precision = \InfluxDB\Database::PRECISION_NANOSECONDS)
    {
        return $this->getWriteConnection()->writePoints($points, $precision);
    }

}