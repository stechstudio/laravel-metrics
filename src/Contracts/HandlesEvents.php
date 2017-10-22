<?php
namespace STS\Metrics\Contracts;

use STS\Metrics\Metric;

interface HandlesEvents
{
    /**
     * @param Metric $metric
     *
     * @return mixed
     */
    public function add(Metric $metric);

    /**
     * @return mixed
     */
    public function flush();
}