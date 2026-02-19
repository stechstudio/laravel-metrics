<?php
use STS\Metrics\Metric;
use STS\Metrics\Facades\Metrics;

class MetricTest extends TestCase
{
    public function testSelfAddingToDefaultDriver()
    {
        $this->setupInfluxDB();

        (new Metric("my_metric", 5))
            ->setTags(['foo' => 'bar'])
            ->add();

        $this->assertEquals(1, count(Metrics::getMetrics()));
        $this->assertEquals("my_metric", Metrics::getMetrics()[0]->getName());
        $this->assertEquals(5, Metrics::getMetrics()[0]->getValue());
    }

    public function testCreatedFromDriver()
    {
        $this->setupInfluxDB();

        // Since it is created from the driver, there is no need to call add() at the end;
        Metrics::create("my_metric")
            ->setValue(5)
            ->setTags(['foo' => 'bar']);

        // But it won't be a 'point' until we flush. This happens at the end of the PHP process.
        Metrics::flush();

        $this->assertEquals(1, count($GLOBALS['points']));
        $this->assertEquals("my_metric", $GLOBALS['points'][0]->getMeasurement());
    }

    public function testDefaultTimestampWhenAdding()
    {
        $metric = new Metric('my_metric', 1);

        $this->assertNull($metric->getTimestamp());

        Metrics::add($metric);

        $this->assertInstanceOf(\DateTime::class, $metric->getTimestamp());
    }

    public function testDefaultTimestampWhenCreatingFromDriver()
    {
        Metrics::create("my_metric")
            ->setValue(5)
            ->setTags(['foo' => 'bar']);

        $this->assertInstanceOf(\DateTime::class, Metrics::getMetrics()[0]->getTimestamp());
    }

    public function testSetExtraWithClosure()
    {
        $metric = new Metric("my_metric", 1);
        $metric->setExtra(fn() => ['foo' => 'bar']);

        $this->assertEquals(['foo' => 'bar'], $metric->getExtra());
    }

    public function testClosureIsEvaluatedEachTime()
    {
        $counter = 0;

        $metric = new Metric("my_metric", 1);
        $metric->setExtra(function () use (&$counter) {
            $counter++;
            return ['count' => $counter];
        });

        $this->assertEquals(['count' => 1], $metric->getExtra());
        $this->assertEquals(['count' => 2], $metric->getExtra());
    }

    public function testAddExtraAfterClosureSet()
    {
        $metric = new Metric("my_metric", 1);
        $metric->setExtra(fn() => ['foo' => 'bar']);
        $metric->addExtra('baz', 'qux');

        $this->assertEquals(['foo' => 'bar', 'baz' => 'qux'], $metric->getExtra());
    }

    public function testGivenTimestampIsntChanged()
    {
        $metric = new Metric('my_metric', 1);
        $time = time();

        $metric->setTimestamp($time);

        $this->assertEquals($time, $metric->getTimestamp());

        Metrics::add($metric);

        $this->assertEquals($time, $metric->getTimestamp());
    }
}
