<?php

use STS\Metrics\Drivers\InfluxDB;

class InfluxDBV2DriverTest extends TestCase
{

    public function testUdpWriteClient()
    {
        $this->setupInfluxDB2(['tcp_port' => 123, 'udp_port' => 456], false);

        $this->assertInstanceOf(
            \InfluxDB2\UdpWriter::class,
            app(InfluxDB::class)->getWriteConnection()
        );
    }

    public function testTcpWriteClient()
    {
        $this->setupInfluxDB2(['tcp_port' => 123]);

        $this->assertInstanceOf(
            \InfluxDB2\WriteApi::class,
            app(InfluxDB::class)->getWriteConnection()
        );
    }

    public function testNanoSecondTimestamp()
    {
        $this->setupInfluxDB();

        $influx = app(InfluxDB::class);

        $this->assertEquals(1508713728000000000, $influx->getNanoSecondTimestamp(1508713728000000000));
        $this->assertEquals(1508713728000000000, $influx->getNanoSecondTimestamp(1508713728));
        $this->assertEquals(1508713728000000000, $influx->getNanoSecondTimestamp(new \DateTime('@1508713728')));
    }

}