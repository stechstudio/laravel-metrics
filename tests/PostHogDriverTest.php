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

    public function testFormatWithClosureExtra()
    {
        $this->setupPostHog();

        $metric = (new \STS\Metrics\Metric("file_uploaded"))
            ->setExtra(fn() => ["foo" => "bar"])
            ->setValue(5);

        $formatted = app(PostHog::class)->format($metric);

        $this->assertEquals('bar', $formatted['properties']['foo']);
        $this->assertEquals(5, $formatted['properties']['value']);
    }

    public function testDriverExtraWithClosure()
    {
        $this->setupPostHog();

        $driver = app(PostHog::class);
        $driver->setExtra(fn() => ['driver_key' => 'driver_value']);

        $metric = (new \STS\Metrics\Metric("file_uploaded"));

        $formatted = $driver->format($metric);

        $this->assertEquals('driver_value', $formatted['properties']['driver_key']);
    }

    public function testUserIdResolvedLazily()
    {
        $this->setupPostHog();

        $callCount = 0;
        $driver = app(PostHog::class);
        $driver->resolveUserIdWith(function () use (&$callCount) {
            $callCount++;
            return 'user_' . $callCount;
        });

        $metric1 = new \STS\Metrics\Metric("event_one");
        $metric2 = new \STS\Metrics\Metric("event_two");

        $formatted1 = $driver->format($metric1);
        $formatted2 = $driver->format($metric2);

        $this->assertEquals('user_1', $formatted1['distinctId']);
        $this->assertEquals('user_2', $formatted2['distinctId']);
    }

    public function testCustomUserIdResolver()
    {
        $this->setupPostHog();

        $driver = app(PostHog::class);
        $driver->resolveUserIdWith(fn() => 'custom-user-42');

        $metric = new \STS\Metrics\Metric("file_uploaded");
        $formatted = $driver->format($metric);

        $this->assertEquals('custom-user-42', $formatted['distinctId']);
    }

    public function testUserIdIsNullWithoutResolver()
    {
        $driver = new PostHog();

        $metric = new \STS\Metrics\Metric("test");
        $formatted = $driver->format($metric);

        $this->assertNull($formatted['distinctId']);
    }

    public function testNoDefaultValue()
    {
        $this->setupPostHog();

        $metric = (new \STS\Metrics\Metric("file_uploaded"));

        $formatted = app(PostHog::class)->format($metric);

        $this->assertFalse(isset($formatted['properties']['value']));
    }
}
