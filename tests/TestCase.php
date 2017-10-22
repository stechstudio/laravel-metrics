<?php
class TestCase extends Orchestra\Testbench\TestCase
{
    protected function getPackageProviders($app)
    {
        return ['STS\EventMetrics\MetricsServiceProvider'];
    }

    protected function getPackageAliases($app)
    {
        return [
            'Metrics' => 'STS\EventMetrics\MetricsFacade'
        ];
    }
}

class InfluxDBMock extends \InfluxDB\Database
{
    public function writePoints(array $points, $precision = \InfluxDB\Database::PRECISION_NANOSECONDS, $retentionPolicy = null)
    {
        $GLOBALS['points'] = $points;

        return true;
    }
}