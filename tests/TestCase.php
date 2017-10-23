<?php
class TestCase extends Orchestra\Testbench\TestCase
{
    protected function getPackageProviders($app)
    {
        return ['STS\Metrics\MetricsServiceProvider', 'Aws\Laravel\AwsServiceProvider'];
    }

    protected function getPackageAliases($app)
    {
        return [
            'Metrics' => 'STS\Metrics\MetricsFacade'
        ];
    }

    protected function setupInfluxDB($config = [])
    {
        app('config')->set('metrics.default', 'influxdb');
        app('config')->set('metrics.backends.influxdb', array_merge([
            'username' => 'foo',
            'password' => 'bar',
            'host' => 'localhost',
            'database' => 'baz',
        ], $config));

        Metrics::setWriteConnection(new InfluxDatabaseMock("baz", Metrics::getWriteConnection()->getClient()));
    }

    protected function setupCloudWatch($config = [])
    {
        app('config')->set('metrics.default', 'cloudwatch');
        app('config')->set('metrics.backends.cloudwatch.namespace', 'STS\\Test');

        app('config')->set('aws', [
            'credentials' => [
                'key' => 'key',
                'secret' => 'secret'
            ],
            'region' => 'us-east-1',
            'version' => 'latest'
        ]);
    }
}

/**
 * We want our Influx client to "flush" metrics simply by writing them to $GLOBALS. Yeah, I could
 * use a mock library. This seems easier.
 */
class InfluxDatabaseMock extends \InfluxDB\Database
{
    public function writePoints(array $points, $precision = \InfluxDB\Database::PRECISION_NANOSECONDS, $retentionPolicy = null)
    {
        $GLOBALS['points'] = $points;

        return true;
    }
}
