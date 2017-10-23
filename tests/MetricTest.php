<?php
class MetricTest extends TestCase
{
    public function testSelfAdding()
    {
        $this->setupInfluxDB();

        (new \STS\Metrics\Metric("my_metric"))
            ->setValue(5)
            ->setTags(['foo' => 'bar'])
            ->add();

        $this->assertEquals(1, count(Metrics::getPoints()));
        $this->assertEquals("my_metric", Metrics::getPoints()[0]->getMeasurement());
    }
}