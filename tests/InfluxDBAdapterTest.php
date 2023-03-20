<?php

use STS\Metrics\Adapters\AbstractInfluxDBAdapter;

class AdapterStub extends AbstractInfluxDBAdapter
{
    public function point($measurement, $value = null, $tags = [], $additionalFields = [], $timestamp = null)
    {
        
    }

    public function writePoints($points, $precision = null)
    {
        
    }
}

class InfluxDBAdapterTest extends TestCase
{

    public function testNanoSecondTimestamp()
    {
        $stub = new AdapterStub(null, null);

        $this->assertEquals(1508713728000000000, $stub->getNanoSecondTimestamp(1508713728000000000));
        $this->assertEquals(1508713728000000000, $stub->getNanoSecondTimestamp(1508713728));
        $this->assertEquals(1508713728000000000, $stub->getNanoSecondTimestamp(new \DateTime('@1508713728')));
    }

}