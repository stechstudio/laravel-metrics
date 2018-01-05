<?php
use STS\Metrics\Drivers\Null;

class NullDriverTest extends TestCase
{
    public function testEmptyFormat()
    {
        $driver = app(Null::class);

        $metric = (new \STS\Metrics\Metric("my_metric"));

        $this->assertEquals([], $driver->format($metric));
    }

    public function testDoesntFlush()
    {
        $driver = app(Null::class);

        $metric = (new \STS\Metrics\Metric("my_metric"));
        $driver->add($metric);

        // Make sure we DO keep track of metrics
        $this->assertEquals(1, count($driver->getMetrics()));

        // But nothing happens when we flush
        $driver->flush();
    }
}
