<?php
namespace STS\Metrics\Contracts;

/**
 * Interface ShouldReportMetric
 * @package STS\EventMetrics
 */
interface ShouldReportMetric
{
    /**
     * @return string
     */
    public function getMetricName();

    /**
     * @return mixed
     */
    public function getMetricValue();

    /**
     * @return array
     */
    public function getMetricTags();

    /**
     * @return array
     */
    public function getMetricExtra();

    /**
     * @return mixed
     */
    public function getMetricTimestamp();
}