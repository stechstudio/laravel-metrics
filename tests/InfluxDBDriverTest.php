<?php
use STS\Metrics\Drivers\InfluxDB;
use STS\Metrics\MetricsManager;

class InfluxDBDriverTest extends TestCase
{
    public function testUdpWriteClient()
    {
        app('config')->set('metrics.default', 'influxdb');
        app('config')->set('metrics.backends.influxdb', [
            'username' => 'foo',
            'password' => 'bar',
            'host' => 'localhost',
            'database' => 'baz',
            'tcp_port' => 123,
            'udp_port' => 456
        ]);

        // Since we provided a UDP port, that will be our write client
        $this->assertInstanceOf(
            \InfluxDB\Driver\UDP::class,
            Metrics::getWriteConnection()->getClient()->getDriver()
        );
    }

    public function testTcpWriteClient()
    {
        app('config')->set('metrics.default', 'influxdb');
        app('config')->set('metrics.backends.influxdb', [
            'username' => 'foo',
            'password' => 'bar',
            'host' => 'localhost',
            'database' => 'baz',
            'tcp_port' => 123
        ]);

        // With UDP port, we will get a TCP client
        $this->assertInstanceOf(
            \InfluxDB\Driver\Guzzle::class,
            Metrics::getWriteConnection()->getClient()->getDriver()
        );
    }

    public function testNanoSecondTimestamp()
    {
        app('config')->set('metrics.default', 'influxdb');
        app('config')->set('metrics.backends.influxdb', [
            'username' => 'foo',
            'password' => 'bar',
            'host' => 'localhost',
            'database' => 'baz',
            'tcp_port' => 123
        ]);

        $this->assertEquals(1508713728000000000, Metrics::getNanoSecondTimestamp(1508713728000000000));
        $this->assertEquals(1508713728000000000, Metrics::getNanoSecondTimestamp(1508713728));
        $this->assertEquals(1508713728000000000, Metrics::getNanoSecondTimestamp(new \DateTime('@1508713728')));
    }
}