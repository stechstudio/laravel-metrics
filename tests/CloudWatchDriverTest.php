<?php
use STS\Metrics\Drivers\CloudWatch;

class CloudWatchDriverTest extends TestCase
{
    public function testFormat()
    {
        $this->setupCloudWatch();

        $metric = (new \STS\Metrics\Metric("file_uploaded"))
            ->setResolution(1)
            ->setValue(50)
            ->setUnit('Megabytes')
            ->setTags(['user' => 54]);

        $formatted = app(CloudWatch::class)->format($metric);

        $this->assertEquals("file_uploaded", $formatted['MetricName']);
        $this->assertEquals(50, $formatted['Value']);
        $this->assertEquals(54, $formatted['Dimensions'][0]['Value']);
        $this->assertEquals(1, $formatted['StorageResolution']);
        $this->assertEquals('Megabytes', $formatted['Unit']);
    }

    public function testDefaultTimestampFormatting()
    {
        $this->setupCloudWatch();

        $metric = (new \STS\Metrics\Metric("file_uploaded"))
            ->setResolution(1)
            ->setValue(50)
            ->setUnit('Megabytes')
            ->setTags(['user' => 54]);

        Metrics::add($metric);

        $this->assertTrue(is_int(Metrics::format($metric)['Timestamp']));
    }

    public function testDefaultTagsExtra()
    {
        $this->setupCloudWatch();
        $driver = app(CloudWatch::class);

        $driver->setTags(['tag1' => 'tag_value'])->setExtra(['extra1' => 'extra_value']);

        $metric = (new \STS\Metrics\Metric("my_metric"))
            ->setTags(['foo' => 'bar']);

        $formatted = $driver->format($metric);

        $this->assertCount(2, $formatted['Dimensions']);
        $this->assertEquals("tag1", $formatted['Dimensions'][0]['Name']);
        $this->assertEquals("tag_value", $formatted['Dimensions'][0]['Value']);
        $this->assertEquals("foo", $formatted['Dimensions'][1]['Name']);
        $this->assertEquals("bar", $formatted['Dimensions'][1]['Value']);
    }

    public function testPassthru()
    {
        $this->setupCloudWatch([], false);
        $driver = app(CloudWatch::class);

        // This call passes through our driver to the underlying influx CloudWatchClient class
        $this->assertInstanceOf(\Aws\Api\Service::class, $driver->getApi());
    }

    public function testZeroValue()
    {
        $this->setupCloudWatch();

        $metric = (new \STS\Metrics\Metric("file_uploaded"))
            ->setValue(0);

        $formatted = app(CloudWatch::class)->format($metric);

        $this->assertEquals(0, $formatted['Value']);
    }
}
