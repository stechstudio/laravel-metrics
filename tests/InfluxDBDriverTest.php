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

    public function testDefaultTagsExtra()
    {
        $this->setupInfluxDB();

        $driver = app(InfluxDB::class);

        $driver->setTags(['tag1' => 'tag_value'])->setExtra(['extra1' => 'extra_value']);

        $metric = (new \STS\Metrics\Metric("my_metric"))
            //->setValue(1)
            ->setTags(['foo' => 'bar']);

        $point = $driver->format($metric);

        $this->assertCount(2, $point->getTags());
        $this->assertEquals("tag_value", $point->getTags()['tag1']);

        $this->assertCount(2, $point->getFields());
        $this->assertEquals('"extra_value"', $point->getFields()['extra1']);
    }

    public function testPassthru()
    {
        $this->setupInfluxDB(['database' => 'dbname'], false);
        $driver = app(InfluxDB::class);

        // This call passes through our driver to the underlying influx Database class
        $this->assertEquals("dbname", $driver->getName());
    }
}
