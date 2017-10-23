<?php
namespace STS\Metrics\Contracts;

use STS\Metrics\Metric;

interface ShouldReportMetric
{
    /**
     * @return Metric
     */
    public function createMetric();

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
