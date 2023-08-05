<?php

namespace STS\Metrics\Traits;

use Illuminate\Support\Str;
use STS\Metrics\Metric;

trait ProvidesMetric
{
    /**
     * @return Metric
     */
    public function createMetric(): Metric
    {
        return (new Metric($this->getMetricName()))
            ->setValue($this->getMetricValue())
            ->setUnit($this->getMetricUnit())
            ->setTags($this->getMetricTags())
            ->setExtra($this->getMetricExtra())
            ->setTimestamp($this->getMetricTimestamp())
            ->setResolution($this->getMetricResolution());
    }

    public function getMetricName(): string
    {
        return property_exists($this, 'metricName')
            ? $this->metricName
            : Str::snake((new \ReflectionClass($this))->getShortName());
    }

    public function getMetricValue()
    {
        return property_exists($this, 'metricValue')
            ? $this->metricValue
            : 1;
    }

    public function getMetricUnit(): string|null
    {
        return property_exists($this, 'metricUnit')
            ? $this->metricUnit
            : null;
    }

    public function getMetricTags(): array
    {
        return property_exists($this, 'metricTags')
            ? $this->metricTags
            : [];
    }

    public function getMetricExtra(): array
    {
        return property_exists($this, 'metricExtra')
            ? $this->metricExtra
            : [];
    }

    public function getMetricTimestamp()
    {
        return property_exists($this, 'metricTimestamp')
            ? $this->metricTimestamp
            : new \DateTime;
    }

    public function getMetricResolution(): int|null
    {
        return property_exists($this, 'metricResolution')
            ? $this->metricResolution
            : null;
    }
}
