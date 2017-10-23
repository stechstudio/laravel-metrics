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
}
