<?php
use STS\Metrics\Drivers\InfluxDB;

class InfluxDBDriverTest extends TestCase
{
    public function testUdpWriteClient()
    {
        $this->setupInfluxDB(['tcp_port' => 123, 'udp_port' => 456]);

        // Since we provided a UDP port, that will be our write client
        $this->assertInstanceOf(
            \InfluxDB\Driver\UDP::class,
            app(InfluxDB::class)->getWriteConnection()->getClient()->getDriver()
        );
    }

    public function testTcpWriteClient()
    {
        $this->setupInfluxDB(['tcp_port' => 123]);

        // Without a UDP port, we will get a TCP client
        $this->assertInstanceOf(
            \InfluxDB\Driver\Guzzle::class,
            app(InfluxDB::class)->getWriteConnection()->getClient()->getDriver()
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
