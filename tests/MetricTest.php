<?php
class MetricTest extends TestCase
{
    protected function getEnvironmentSetUp($app)
    {
        app('config')->set('metrics.default', 'influxdb');
        app('config')->set('metrics.backends.influxdb', [
            'username' => 'foo',
            'password' => 'bar',
            'host' => 'localhost',
            'database' => 'baz',
        ]);
    }

    protected function setUp()
    {
        parent::setUp();

        $client = Metrics::driver();
        $client->setTcpConnection(new InfluxDBMock("baz", $client->getTcpConnection()->getClient()));
    }

    public function testSelfAdding()
    {
        (new \STS\Metrics\Metric("my_metric"))
            ->setValue(5)
            ->setTags(['foo' => 'bar'])
            ->add();

        $this->assertEquals(1, count(Metrics::getPoints()));
        $this->assertEquals("my_metric", Metrics::getPoints()[0]->getMeasurement());
    }
}