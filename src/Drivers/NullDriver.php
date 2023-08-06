<?php

namespace STS\Metrics\Drivers;

use STS\Metrics\Metric;

class NullDriver extends AbstractDriver
{
    public function flush(): static
    {
        return $this;
    }

    public function format(Metric $metric): array
    {
        return [];
    }
}
