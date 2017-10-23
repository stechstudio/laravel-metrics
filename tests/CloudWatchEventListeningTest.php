<?php
class CloudWatchEventListeningTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->setupCloudWatch();
    }

    public function testBasicEventMetricAdded()
    {
        event(new BasicCloudWatchEvent);
        Metrics::flush();

        $this->assertEquals(1, count($GLOBALS['metrics']['MetricData']));
        $this->assertEquals("basic_cloud_watch_event", $GLOBALS['metrics']['MetricData'][0]['MetricName']);
    }

    public function testMetricWithAttributes()
    {
        event(new CloudWatchEventWithAttributes);
        Metrics::flush();

        /** @var \InfluxDB\Point $metric */
        $metric = $GLOBALS['metrics']['MetricData'][0];
        $this->assertEquals("order_placed", $metric['MetricName']);
        $this->assertEquals("email", $metric['Dimensions']['source']);
        $this->assertEquals(1508701523, $metric['Timestamp']);
    }

    public function testMetricWithGetters()
    {
        event(new CloudWatchEventWithGetters());
        Metrics::flush();

        /** @var \InfluxDB\Point $metric */
        $metric = $GLOBALS['metrics']['MetricData'][0];
        $this->assertEquals("user_registered", $metric['MetricName']);
        $this->assertEquals("1", $metric['Value']);
        $this->assertEquals(false, $metric['Dimensions']['admin']);
        $this->assertEquals(1508702054, $metric['Timestamp']);
    }
}

class BasicCloudWatchEvent implements \STS\Metrics\Contracts\ShouldReportMetric {
    use STS\Metrics\Traits\ProvidesMetric;
}

class CloudWatchEventWithAttributes implements \STS\Metrics\Contracts\ShouldReportMetric {
    use STS\Metrics\Traits\ProvidesMetric;

    protected $metricName = "order_placed";
    protected $metricValue = 5;
    protected $metricTags = ["source" => "email"];
    protected $metricExtra = ["item_count" => 10];
    protected $metricTimestamp = 1508701523;
}

class CloudWatchEventWithGetters implements \STS\Metrics\Contracts\ShouldReportMetric {
    use STS\Metrics\Traits\ProvidesMetric;

    public function getMetricName()
    {
        return "user_registered";
    }

    public function getMetricValue()
    {
        return 1;
    }

    public function getMetricTags()
    {
        return ["admin" => false];
    }

    public function getMetricExtra()
    {
        return ["company_id" => 50];
    }

    public function getMetricTimestamp()
    {
        return 1508702054;
    }
}
;
