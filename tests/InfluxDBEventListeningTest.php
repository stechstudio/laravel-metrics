<?php
use STS\Metrics\Facades\Metrics;
class InfluxDBEventListeningTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->setupInfluxDB();
    }

    public function testBasicEventMetricAdded()
    {
        event(new BasicEvent);
        Metrics::flush();

        $this->assertEquals(1, count($GLOBALS['points']));
        $this->assertEquals("basic_event", $GLOBALS['points'][0]->getMeasurement());
    }

    public function testMetricWithAttributes()
    {
        event(new EventWithAttributes);
        Metrics::flush();

        /** @var \InfluxDB\Point $point */
        $point = $GLOBALS['points'][0];
        $this->assertEquals("order_placed", $point->getMeasurement());
        $this->assertEquals("5i", $point->getFields()['value']);
        $this->assertEquals("email", $point->getTags()['source']);
        $this->assertEquals('10i', $point->getFields()['item_count']);
        $this->assertEquals(1508701523000000000, $point->getTimestamp());
    }

    public function testMetricWithGetters()
    {
        event(new EventWithGetters());
        Metrics::flush();

        /** @var \InfluxDB\Point $point */
        $point = $GLOBALS['points'][0];
        $this->assertEquals("user_registered", $point->getMeasurement());
        $this->assertEquals("1i", $point->getFields()['value']);
        $this->assertEquals("false", $point->getTags()['admin']);
        $this->assertEquals('50i', $point->getFields()['company_id']);
        $this->assertEquals(1508702054000000000, $point->getTimestamp());
    }
}

class BasicEvent implements \STS\Metrics\Contracts\ShouldReportMetric {
    use STS\Metrics\Traits\ProvidesMetric;
}

class EventWithAttributes implements \STS\Metrics\Contracts\ShouldReportMetric {
    use STS\Metrics\Traits\ProvidesMetric;

    protected $metricName = "order_placed";
    protected $metricValue = 5;
    protected $metricTags = ["source" => "email"];
    protected $metricExtra = ["item_count" => 10];
    protected $metricTimestamp = 1508701523;
}

class EventWithGetters implements \STS\Metrics\Contracts\ShouldReportMetric {
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
