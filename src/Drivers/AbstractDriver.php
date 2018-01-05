<?php

namespace STS\Metrics\Drivers;

use STS\Metrics\Metric;

/**
 * Class AbstractDriver
 * @package STS\Metrics\Drivers
 */
abstract class AbstractDriver
{
    /**
     * @var array
     */
    protected $metrics = [];

    /**
     * @var array
     */
    protected $tags = [];

    /**
     * @var array
     */
    protected $extra = [];

    /**
     * @param $name
     * @param $value
     *
     * @return Metric
     */
    public function create($name, $value = null)
    {
        $metric = new Metric($name, $value, $this);
        $this->metrics[] = &$metric;

        return $metric;
    }

    /**
     * @param Metric $metric
     *
     * @return $this
     */
    public function add(Metric $metric)
    {
        $metric->setDriver($this);

        $this->metrics[] = $metric;

        return $this;
    }

    /**
     * @return array
     */
    public function getMetrics()
    {
        return $this->metrics;
    }

    /**
     * Set default tags to be merged in on all metrics
     *
     * @param array $tags
     *
     * @return $this
     */
    public function setTags(array $tags)
    {
        $this->tags = $tags;

        return $this;
    }

    /**
     * Set default extra to be merged in on all metrics
     *
     * @param array $extra
     *
     * @return $this
     */
    public function setExtra(array $extra)
    {
        $this->extra = $extra;

        return $this;
    }

    /**
     * @param Metric $metric
     *
     * @return mixed
     */
    abstract public function format(Metric $metric);

    /**
     * @return $this
     */
    abstract public function flush();
}
