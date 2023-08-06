<?php
use STS\Metrics\Drivers\PostHog;
use STS\Metrics\Facades\Metrics;
class PostHogDriverTest extends TestCase
{
    public function testFormat()
    {
        $this->setupPostHog();

        $metric = (new \STS\Metrics\Metric("file_uploaded"))
            ->setExtra(["foo" => "bar"])
            ->setValue(5);

        $formatted = app(PostHog::class)->format($metric);

        $this->assertEquals("file_uploaded", $formatted['event']);
        $this->assertEquals('bar', $formatted['properties']['foo']);
        $this->assertEquals(5, $formatted['properties']['value']);

    }

    public function testNoDefaultValue()
    {
        $this->setupPostHog();

        $metric = (new \STS\Metrics\Metric("file_uploaded"));

        $formatted = app(PostHog::class)->format($metric);

        $this->assertFalse(isset($formatted['properties']['value']));
    }
}
