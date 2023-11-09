<?php

use STS\Metrics\Drivers\PrometheusDriver;
use STS\Metrics\MetricType;
use STS\Metrics\Metric;
use Prometheus\Counter;

class PrometheusDriverTest extends TestCase
{
    public function testFormat()
    {
        /** @var PrometheusDriver $driver */
        $driver = app(PrometheusDriver::class);

        $metric = (new Metric("file_uploaded"))
            ->setDriver($driver)
            ->setType(MetricType::COUNTER)
            ->setTags(['foo' => 'bar', 'test' => 1])
            ->setValue(5);

        $formatted = app(PrometheusDriver::class)->format($metric);

        $this->assertTrue($formatted instanceof Counter);
        $this->assertEquals(['foo', 'test'], $formatted->getLabelNames());
        $this->assertEquals('counter', $formatted->getType());

    }

    public function testItThrowsOnUnknownType()
    {
        /** @var PrometheusDriver $driver */
        $driver = app(PrometheusDriver::class);

        $metric = (new Metric("file_uploaded"))
            ->setDriver($driver)
            ->setTags(['foo' => 'bar', 'test' => 1])
            ->setValue(5);

        $this->expectException(\UnhandledMatchError::class);
        app(PrometheusDriver::class)->format($metric);
    }

    public function testItCanFormatAllMetricsCorrectly()
    {
        /** @var PrometheusDriver $driver */
        $driver = app(PrometheusDriver::class);

        $this->assertEquals("\n", $driver->formatted());

         (new Metric("file_uploaded"))
            ->setType(MetricType::COUNTER)
            ->setDriver($driver)
            ->setTags(['foo' => 'bar', 'test' => 1])
            ->setValue(5)
            ->add();

        $this->assertEquals("# HELP app_file_uploaded file_uploaded\n# TYPE app_file_uploaded counter\napp_file_uploaded{foo=\"bar\",test=\"1\"} 5\n", $driver->formatted());

        // Second format of all metrics produces the same result, no increment on the counter or flushing the registry
        $this->assertEquals("# HELP app_file_uploaded file_uploaded\n# TYPE app_file_uploaded counter\napp_file_uploaded{foo=\"bar\",test=\"1\"} 5\n", $driver->formatted());

        // flushing the driver wipes out all metrics and cleanups the underlying registry
        $driver->flush();
        $this->assertEmpty($driver->getMetrics());
        $this->assertEquals("\n", $driver->formatted());
    }

}
