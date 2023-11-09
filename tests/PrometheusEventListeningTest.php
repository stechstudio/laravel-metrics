<?php

class PrometheusEventListeningTest extends TestCase
{

    protected function setUp(): void
    {
        parent::setUp();

        $this->setupPrometheus();
    }

    public function testBasicCounterMetricAdded()
    {
        event(new BasicPrometheusCounterEvent);

        $driver = app(\STS\Metrics\Drivers\PrometheusDriver::class);
        $this->assertEquals("# HELP app_order_placed order_placed\n# TYPE app_order_placed counter\napp_order_placed{Deployment=\"local\",tenant=\"develop\",source=\"email\"} 5\n", $driver->formatted());

        // flushing, wipes out the stored metrics
        \STS\Metrics\Facades\Metrics::flush();
        $this->assertEquals("\n", $driver->formatted());
    }
}

class BasicPrometheusCounterEvent implements \STS\Metrics\Contracts\ShouldReportMetric {
    use STS\Metrics\Traits\ProvidesMetric;

    protected \STS\Metrics\MetricType $metricType = \STS\Metrics\MetricType::COUNTER;

    protected $metricName = "order_placed";
    protected $metricValue = 5;
    protected $metricTags = ["Deployment" => "local", "tenant" => "develop", "source" => "email"];
    protected $metricExtra = ["item_count" => 10];
    protected $metricTimestamp = 1508701523;
}
