<?php

namespace STS\Metrics;

use STS\Metrics\Drivers\AbstractDriver;

/**
 * Class Metric
 * @package STS\Metrics
 */
class Metric
{
    /**
     * @var AbstractDriver
     */
    protected $driver;
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
     * @var array|\Closure
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

    protected string $namespace = 'app';

    protected ?MetricType $type = null;

    protected ?string $description = null;

    protected ?string $logMessage = null;

    protected string $logLevel = 'info';

    /**
     * Metric constructor.
     *
     * @param $name
     * @param $value
     * @param $driver
     */
    public function __construct($name = null, $value = null, $driver = null)
    {
        $this->setName($name);
        $this->setValue($value);

        $this->driver = $driver;
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

    public function getValue(): mixed
    {
        return $this->value;
    }

    public function setValue(mixed $value): static
    {
        $this->value = $value;

        return $this;
    }

    public function getUnit(): string|null
    {
        return $this->unit;
    }

    public function setUnit(mixed $unit): static
    {
        $this->unit = $unit;

        return $this;
    }

    public function getTags(): array
    {
        return $this->tags;
    }

    public function setTags(array $tags): static
    {
        $this->tags = $tags;

        return $this;
    }

    public function addTag($key, $value): static
    {
        $this->tags[$key] = $value;

        return $this;
    }

    public function getExtra(): array
    {
        $extra = value($this->extra);

        if ($this->logMessage) {
            $extra['message'] = $this->logMessage;
        }

        return $extra;
    }

    public function setExtra(array|\Closure $extra): static
    {
        $this->extra = $extra;

        return $this;
    }

    public function addExtra($key, $value): static
    {
        $this->extra = value($this->extra);
        $this->extra[$key] = $value;

        return $this;
    }

    public function withLog(string $message, string $level = 'info'): static
    {
        $this->logMessage = $message;
        $this->logLevel = $level;

        return $this;
    }

    public function getLogMessage(): ?string
    {
        return $this->logMessage;
    }

    public function getLogLevel(): string
    {
        return $this->logLevel;
    }

    public function getTimestamp(): mixed
    {
        return $this->timestamp;
    }

    public function setTimestamp($timestamp): static
    {
        $this->timestamp = $timestamp;

        return $this;
    }

    public function add(): AbstractDriver
    {
        return $this->getDriver()->add($this);
    }
    public function getDriver(): AbstractDriver
    {
        return $this->driver ?? app('metrics')->driver();
    }

    public function setDriver(AbstractDriver $driver): static
    {
        $this->driver = $driver;

        return $this;
    }

    public function setNamespace(string $namespace): void
    {
        $this->namespace = $namespace;
    }

    public function getNamespace(): string
    {
        return $this->namespace;
    }

    public function format(): mixed
    {
        return $this->getDriver()->format($this);
    }

    public function formatted(): mixed
    {
        return $this->getDriver()->formatted();
    }

    public function getType(): ?MetricType
    {
        return $this->type;
    }

    public function setType(?MetricType $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description = null): static
    {
        $this->description = $description;

        return $this;
    }
}
