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
        $driver = app(\STS\Metrics\Drivers\PrometheusDriver::class);

        $this->assertEquals("# HELP app_order_placed order_placed\n# TYPE app_order_placed counter\napp_order_placed{source=\"email\"} 5\n", $driver->render());
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
