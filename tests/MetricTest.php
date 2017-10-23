<?php
class MetricTest extends TestCase
{
    public function testSelfAddingToDefaultDriver()
    {
        $this->setupInfluxDB();

        (new \STS\Metrics\Metric("my_metric", 5))
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
}
