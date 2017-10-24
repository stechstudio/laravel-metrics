<?php

namespace STS\Metrics\Contracts;

use STS\Metrics\Metric;

interface ShouldReportMetric
{
    /**
     * @return Metric
     */
    public function createMetric();
}
