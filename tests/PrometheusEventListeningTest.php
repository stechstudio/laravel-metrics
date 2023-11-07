<?php

class PrometheusEventListeningTest extends TestCase
{

    protected function setUp(): void
    {
        parent::setUp();

        $this->setupPrometheus();
    }

    public function testBasicEventMetricAdded()
    {
        event(new BasicPrometheusCounterEvent);
        \STS\Metrics\Facades\Metrics::flush();

        $this->expectOutputString("# HELP Test_order_placed order_placed\n# TYPE Test_order_placed counter\nTest_order_placed{email=\"email\"} 5\n");
    }
}

class BasicPrometheusCounterEvent implements \STS\Metrics\Contracts\ShouldReportMetric {
    use STS\Metrics\Traits\ProvidesMetric;

    protected $metricName = "order_placed";
    protected $metricValue = 5;
    protected $metricTags = ["source" => "email"];
    protected $metricExtra = ["item_count" => 10];
    protected $metricTimestamp = 1508701523;
}
