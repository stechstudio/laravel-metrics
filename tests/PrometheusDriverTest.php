<?php

use STS\Metrics\Metric;
use STS\Metrics\MetricsManager;

class PrometheusDriverTest extends TestCase
{
    public function testCanCreateDriver()
    {
        app('config')->set('metrics.default', 'prometheus');

        $manager = app(MetricsManager::class);

        $this->assertInstanceOf(\STS\Metrics\Drivers\PrometheusDriver::class, $manager->driver());
    }

    public function testSimpleFormatting()
    {
        $driver = app(\STS\Metrics\Drivers\PrometheusDriver::class);

        $metric = (new Metric('some_metric', 123))->setTags([
            'source' => 'tenant one',
            'user' => 54,
            'session' => 'abc123',
            'avg_time_ms' => 123,
        ]);
        $data = $driver->format($metric)[0];

        $this->assertEquals(
            '# HELP some_metric help
# TYPE some_metric type
some_metric{source="tenant one",user="54",session="abc123",avg_time_ms="123"} 123
',
            $data
        );
    }
}
