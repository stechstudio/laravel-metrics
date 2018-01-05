<?php

namespace STS\Metrics\Drivers;

use STS\Metrics\Metric;

/**
 * Class Null
 * @package STS\Metrics\Drivers
 */
class NullDriver extends AbstractDriver
{
    /**
     * @return $this
     */
    public function flush()
    {
        return $this;
    }

    /**
     * @param Metric $metric
     *
     * @return array
     */
    public function format(Metric $metric)
    {
        return [];
    }
}
