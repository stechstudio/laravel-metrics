<?php
namespace STS\Metrics;

use STS\Metrics\Contracts\HandlesMetrics;
use STS\Metrics\MetricsFacade as Metrics;

/**
 * Class Metric
 * @package STS\Metrics
 */
class Metric
{
    /**
     * @var HandlesMetrics
     */
    protected $creator;
    /**
     * @var
     */
    protected $name;
    /**
     * @var
     */
    protected $value;
    /**
     * @var string
     */
    protected $unit;
    /**
     * @var array
     */
    protected $tags = [];
    /**
     * @var array
     */
    protected $extra = [];
    /**
     * @var
     */
    protected $timestamp;
    /**
     * @var int
     */
    protected $resolution;

    /**
     * Metric constructor.
     *
     * @param $name
     * @param $value
     * @param $creator
     */
    public function __construct($name = null, $value = null, $creator = null)
    {
        $this->setName($name);
        $this->setValue($value);

        $this->creator = $creator;
    }

    /**
     * @return int
     */
    public function getResolution()
    {
        return $this->resolution;
    }

    /**
     * @param int $resolution
     *
     * @return $this
     */
    public function setResolution($resolution)
    {
        $this->resolution = $resolution;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     *
     * @return $this
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @return string
     */
    public function getUnit()
    {
        return $this->unit;
    }

    /**
     * @param string $unit
     *
     * @return $this
     */
    public function setUnit($unit)
    {
        $this->unit = $unit;

        return $this;
    }

    /**
     * @return array
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
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
     * @param $key
     * @param $value
     */
    public function addTag($key, $value)
    {
        $this->tags[$key] = $value;
    }

    /**
     * @return array
     */
    public function getExtra()
    {
        return $this->extra;
    }

    /**
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
     * @param $key
     * @param $value
     */
    public function addExtra($key, $value)
    {
        $this->extra[$key] = $value;
    }

    /**
     * @return mixed
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }

    /**
     * @param mixed $timestamp
     *
     * @return $this
     */
    public function setTimestamp($timestamp)
    {
        $this->timestamp = $timestamp;

        return $this;
    }

    /**
     * @return mixed
     */
    public function add()
    {
        if($this->creator) {
            return $this->creator->add($this);
        }

        return Metrics::add($this);
    }
}
