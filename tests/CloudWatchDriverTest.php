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
        $this->assertEquals(54, $formatted['Dimensions']['user']);
        $this->assertEquals(1, $formatted['StorageResolution']);
        $this->assertEquals('Megabytes', $formatted['Unit']);
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
        $this->assertEquals("tag_value", $formatted['Dimensions']['tag1']);
    }

    public function testPassthru()
    {
        $this->setupCloudWatch([], false);
        $driver = app(CloudWatch::class);

        // This call passes through our driver to the underlying influx CloudWatchClient class
        $this->assertInstanceOf(\Aws\Api\Service::class, $driver->getApi());
    }
}
