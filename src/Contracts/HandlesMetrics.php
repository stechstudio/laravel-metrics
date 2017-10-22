<?php
namespace STS\Metrics\Contracts;

use STS\Metrics\Metric;

interface HandlesMetrics
{
    /**
     * @param $name
     *
     * @return Metric
     */
    public function create($name);

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

    /**
     * @return array
     */
    public function getMetrics();
}