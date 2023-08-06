<?php
use STS\Metrics\Drivers\LogDriver;
use STS\Metrics\Facades\Metrics;
class LogDriverTest extends TestCase
{

    public function testCanCreateDriver()
    {
        app('config')->set('metrics.default', 'log');

        $manager = app(\STS\Metrics\MetricsManager::class);

        $this->assertInstanceOf(LogDriver::class, $manager->driver());
    }

    public function testFormat()
    {
        $driver = app(LogDriver::class);
        $metric = (new \STS\Metrics\Metric("my_metric"));
        $this->assertEquals(
            array_filter([
                'name' => $metric->getName(),
                'value' => $metric->getValue(),
                'resolution' => $metric->getResolution(),
                'unit' => $metric->getUnit(),
                'tags' => $metric->getTags(),
                'extra' => $metric->getExtra(),
                'timestamp' => $metric->getTimestamp()
            ]),
            $driver->format($metric)
        );
    }
}
