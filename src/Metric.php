<?php
namespace STS\Metrics;

use STS\Metrics\Contracts\HandlesMetrics;
use Metrics;

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
     * Metric constructor.
     *
     * @param $name
     * @param $creator
     */
    function __construct($name = null, $creator = null)
    {
        $this->name = $name;
        $this->creator = $creator;
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