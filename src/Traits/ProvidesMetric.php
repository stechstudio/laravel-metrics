<?php
namespace STS\Metrics\Traits;

use STS\Metrics\Metric;

/**
 * Class ProvidesMetric
 * @package STS\Metrics\Traits
 */
trait ProvidesMetric
{
    /**
     * @return Metric
     */
    public function createMetric()
    {
        return (new Metric($this->getMetricName()))
            ->setValue($this->getMetricValue())
            ->setUnit($this->getMetricUnit())
            ->setTags($this->getMetricTags())
            ->setExtra($this->getMetricExtra())
            ->setTimestamp($this->getMetricTimestamp())
            ->setResolution($this->getMetricResolution());
    }

    /**
     * @return string
     */
    public function getMetricName()
    {
        return property_exists($this, 'metricName')
            ? $this->metricName
            : snake_case((new \ReflectionClass($this))->getShortName());
    }

    /**
     * @return mixed
     */
    public function getMetricValue()
    {
        return property_exists($this, 'metricValue')
            ? $this->metricValue
            : null;
    }

    /**
     * @return string
     */
    public function getMetricUnit()
    {
        return property_exists($this, 'metricUnit')
            ? $this->metricUnit
            : null;
    }

    /**
     * @return array
     */
    public function getMetricTags()
    {
        return property_exists($this, 'metricTags')
            ? $this->metricTags
            : [];
    }

    /**
     * @return array
     */
    public function getMetricExtra()
    {
        return property_exists($this, 'metricExtra')
            ? $this->metricExtra
            : [];
    }

    /**
     * @return mixed
     */
    public function getMetricTimestamp()
    {
        return property_exists($this, 'metricTimestamp')
            ? $this->metricTimestamp
            : null;
    }

    /**
     * @return int
     */
    public function getMetricResolution()
    {
        return property_exists($this, 'metricResolution')
            ? $this->metricResolution
            : null;
    }
}
