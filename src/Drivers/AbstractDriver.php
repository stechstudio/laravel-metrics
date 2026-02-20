<?php

namespace STS\Metrics\Drivers;

use Illuminate\Support\Str;
use STS\Metrics\Metric;

abstract class AbstractDriver
{
    protected array $metrics = [];

    protected array $tags = [];

    protected array|\Closure $extra = [];

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

    public function getExtra(): array
    {
        return value($this->extra);
    }

    /**
     * Set default extra to be merged in on all metrics
     */
    public function setExtra(array|\Closure $extra): static
    {
        $this->extra = $extra;

        return $this;
    }

    protected ?\Closure $userIdResolver = null;

    public function resolveUserIdUsing(\Closure $resolver): static
    {
        $this->userIdResolver = $resolver;

        return $this;
    }

    public function getUserId(): mixed
    {
        if ($this->userIdResolver) {
            return call_user_func($this->userIdResolver, $this);
        }

        return auth()->check()
            ? auth()->id()
            : $this->getAnonymousId();
    }

    public function getAnonymousId(): string
    {
        if (session()->isStarted()) {
            return session()->remember('metrics.anonymous_id', fn() => Str::uuid7()->toString());
        }

        static $anonymousId = null;

        return $anonymousId ??= Str::uuid7()->toString();
    }

    /**
     * Implement this, when the driver needs to expose metrics to be polled by a third party service such as prometheus
     */
    public function formatted(): mixed
    {
        return null;
    }

    abstract public function format(Metric $metric);

    abstract public function flush(): static;
}
