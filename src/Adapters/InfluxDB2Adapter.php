<?php

namespace STS\Metrics\Adapters;

use InfluxDB2\Client;
use InfluxDB2\Point;
use Throwable;

class InfluxDB2Adapter extends AbstractInfluxDBAdapter
{
    public function __construct(
        Client $client,
        bool $useUdp = false
    )
    {
        $this->readConnection = $client->createQueryApi();
        $this->writeConnection = $useUdp
            ? $client->createUdpWriter()
            : $client->createWriteApi();
    }

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
            $tags,
            array_merge(compact('value'), $fields),
            $this->getNanoSecondTimestamp($timestamp)
        );
    }

    /**
     * @throws Throwable
     */
    public function writePoints(array $points, $precision = Point::DEFAULT_WRITE_PRECISION)
    {
        $this->getWriteConnection()->write($points, $precision);
        return true;
    }

}