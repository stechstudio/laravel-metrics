<?php

namespace STS\Metrics\Traits;

use Illuminate\Support\Str;
use STS\Metrics\Metric;
use STS\Metrics\MetricType;

trait ProvidesMetric
{
    /**
     * @return Metric
     */
    public function createMetric(): Metric
    {
        return (new Metric($this->getMetricName()))
            ->setType($this->getMetricType())
            ->setValue($this->getMetricValue())
            ->setUnit($this->getMetricUnit())
            ->setTags($this->getMetricTags())
            ->setExtra($this->getMetricExtra())
            ->setTimestamp($this->getMetricTimestamp())
            ->setResolution($this->getMetricResolution())
            ->setDescription($this->getMetricDescription());
    }

    public function getMetricName(): string
    {
        return property_exists($this, 'metricName')
            ? $this->metricName
            : Str::snake((new \ReflectionClass($this))->getShortName());
    }

    public function getMetricValue(): mixed
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

    public function getMetricType(): ?MetricType
    {
        return property_exists($this, 'metricType')
            ? $this->metricType
            : null;
    }

    public function getMetricDescription(): ?string
    {
        return property_exists($this, 'metricDescription')
            ? $this->metricDescription
            : null;
    }
}
