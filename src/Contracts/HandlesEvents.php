<?php
namespace STS\Metrics\Contracts;

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