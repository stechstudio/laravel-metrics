<?php
namespace STS\Metrics\Traits;

trait ProvidesMetric
{
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
    public function getMetricFields()
    {
        return property_exists($this, 'metricFields')
            ? $this->metricFields
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
}