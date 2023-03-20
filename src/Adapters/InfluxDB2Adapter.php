<?php

namespace STS\Metrics\Adapters;

class InfluxDB2Adapter extends AbstractInfluxDBAdapter
{

    /**
     * @param \InfluxDB2\Client $client
     * @param boolean $useUdp
     */
    public function __construct(
        \InfluxDB2\Client $client,
        $useUdp = false
    )
    {
        $this->readConnection = $client->createQueryApi();
        $this->writeConnection = $useUdp
            ? $client->createWriteApi()
            : $client->createUdpWriter();
    }

    /**
     * @inheritDoc
     */
    public function point($measurement, $value = null, $tags = [], $additionalFields = [], $timestamp = null)
    {
        return new \InfluxDB2\Point(
            $measurement,
            $tags,
            array_merge(compact('value'), $additionalFields),
            $this->getNanoSecondTimestamp($timestamp)
        );
    }

    /**
     * @inheritDoc
     */
    public function writePoints($points, $precision = \InfluxDB2\Point::DEFAULT_WRITE_PRECISION)
    {
        $this->getWriteConnection()->write($points, $precision);
        return true;
    }

}