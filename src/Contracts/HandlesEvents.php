<?php
namespace STS\EventMetrics\Contracts;

use STS\EventMetrics\Contracts\ShouldReportMetric;

interface HandlesEvents
{
    /**
     * @param ShouldReportMetric $event
     *
     * @return mixed
     */
    public function event(ShouldReportMetric $event);

    /**
     * @return mixed
     */
    public function flush();
}