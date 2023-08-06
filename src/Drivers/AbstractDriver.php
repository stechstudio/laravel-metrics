<?php

namespace STS\Metrics\Drivers;

use STS\Metrics\Metric;

abstract class AbstractDriver
{
    protected array $metrics = [];

    protected array $tags = [];

    protected array $extra = [];

    public function create(string $name, $value = null): Metric
    {
        $metric = new Metric($name, $value, $this);
        $this->add($metric);

        return $metric;
    }

    public function add(Metric $metric): static
    {
        $metric->setDriver($this);

        if($metric->getTimestamp() === null) {
            $metric->setTimestamp(new \DateTime);
        }

        $this->metrics[] = $metric;

        return $this;
    }

    public function getMetrics(): array
    {
        return $this->metrics;
    }

    /**
     * Set default tags to be merged in on all metrics
     */
    public function setTags(array $tags): static
    {
        $this->tags = $tags;

        return $this;
    }

    /**
     * Set default extra to be merged in on all metrics
     */
    public function setExtra(array $extra): static
    {
        $this->extra = $extra;

        return $this;
    }

    abstract public function format(Metric $metric);

    abstract public function flush(): static;
}
