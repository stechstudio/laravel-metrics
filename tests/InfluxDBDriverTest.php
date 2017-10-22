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

        /** @var InfluxDB $client */
        $client = app(MetricsManager::class)->driver();

        // Since we provided a UDP port, that will be our write client
        $this->assertInstanceOf(
            \InfluxDB\Driver\UDP::class,
            $client->getWriteConnection()->getClient()->getDriver()
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

        /** @var InfluxDB $client */
        $client = app(MetricsManager::class)->driver();

        // With UDP port, we will get a TCP client
        $this->assertInstanceOf(
            \InfluxDB\Driver\Guzzle::class,
            $client->getWriteConnection()->getClient()->getDriver()
        );
    }


}