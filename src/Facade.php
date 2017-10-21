<?php
namespace STS\EventMetrics;

/**
 * Class Facade
 * @package STS\EventMetrics
 */
class Facade extends \Illuminate\Support\Facades\Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return InfluxDB::class;
    }
}