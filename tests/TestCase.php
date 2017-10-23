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

        $mock = Mockery::mock(\InfluxDB\Database::class, ["db_name", Metrics::getWriteConnection()->getClient()])->makePartial();
        $mock->shouldReceive('writePoints')
            ->andReturnUsing(function($points) {
                $GLOBALS['points'] = $points;
            });

        Metrics::setWriteConnection($mock);
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

        $mock = Mockery::mock(\Aws\CloudWatch\CloudWatchClient::class)->makePartial();
        $mock->shouldReceive('putMetricData')
            ->andReturnUsing(function($args) {
                $GLOBALS['metrics'] = $args;
            });
        Metrics::setClient($mock);
    }
}
