<?php
use STS\Metrics\MetricsServiceProvider;
use STS\Metrics\Facades\Metrics;

class TestCase extends Orchestra\Testbench\TestCase
{
    protected function getPackageProviders($app)
    {
        return [MetricsServiceProvider::class];
    }

    protected function getPackageAliases($app)
    {
        return [
            'Metrics' => Metrics::class
        ];
    }

    protected function setupInfluxDB($config = [], $mock = true)
    {
        app('config')->set('metrics.default', 'influxdb');
        app('config')->set('metrics.backends.influxdb', array_merge([
            'username' => 'foo',
            'password' => 'bar',
            'host' => 'localhost',
            'database' => 'baz',
            'version'  => 1,
            'tcp_port' => 8086
        ], $config));

        if($mock) {
            $mock = Mockery::mock(\InfluxDB\Database::class, ["db_name", Metrics::getWriteConnection()->getClient()])->makePartial();
            $mock->shouldReceive('writePoints')
                ->andReturnUsing(function ($points) {
                    $GLOBALS['points'] = $points;
                });

            Metrics::setWriteConnection($mock);
        }
    }

    protected function setupInfluxDB2($config = [], $mock = true)
    {
        app('config')->set('metrics.default', 'influxdb');
        app('config')->set('metrics.backends.influxdb', array_merge([
            'token' => 'foo',
            'host' => 'localhost',
            'tcp_port' => 8086,
            'database' => 'baz',
            'version' => 2,
            'org' => 'bar'
        ], $config));

        if ($mock) {
            $mock = Mockery::mock(\InfluxDB2\WriteApi::class);
            $mock->shouldReceive('write')
                ->andReturnUsing(function ($points) {
                    $GLOBALS['points'] = $points;
                });
            Metrics::setWriteConnection($mock);
        }
    }

    protected function setupCloudWatch($config = [], $mock = true)
    {
        app('config')->set('metrics.default', 'cloudwatch');
        app('config')->set('metrics.backends.cloudwatch.namespace', 'Testing');
        app('config')->set('metrics.backends.cloudwatch.key', 'Testing');
        app('config')->set('metrics.backends.cloudwatch.secret', 'Testing');

        if($mock) {
            $mock = Mockery::mock(\Aws\CloudWatch\CloudWatchClient::class)->makePartial();
            $mock->shouldReceive('putMetricData')
                ->andReturnUsing(function($args) {
                    $GLOBALS['metrics'] = $args;
                });
            Metrics::setClient($mock);
        }
    }

    protected function setupPostHog($config = [], $mock = true)
    {
        app('config')->set('metrics.default', 'posthog');
        app('config')->set('metrics.backends.posthog.key', 'Testing');
    }

    protected function setupLogDriver($config = [], $mock = true)
    {
        app('config')->set('metrics.default', 'log');

        if ($mock) {
            $mock = Mockery::mock(\Monolog\Logger::class)->makePartial();
            $mock->shouldRecive('info')
            ->andReturnUsing(function ($args) {
                $GLOBALS['metrics'] = $args;
            });
            Metrics::setClient($mock);
        }
    }

}
