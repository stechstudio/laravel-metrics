# Laravel Metrics

[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Quality Score](https://img.shields.io/scrutinizer/g/stechstudio/laravel-metrics.svg?style=flat-square)](https://scrutinizer-ci.com/g/stechstudio/laravel-metrics)
[![Total Downloads](https://img.shields.io/packagist/dt/stechstudio/laravel-metrics.svg?style=flat-square)](https://packagist.org/packages/stechstudio/laravel-metrics)

This package makes it incredibly easy to ship app metrics to backends such as InfluxDB or CloudWatch.

There are two major components: a facade that lets you create metrics on your own, and an event listener to automatically send metrics for Laravel events.
   
## Installation

You know the drill...

```
composer install stechstudio/laravel-metrics
```

If you're running Laravel 5.5, you're done. For Laravel 5.4 and earlier, add the following service provider and facade to `config/app.php`:

```php
'providers' => [
    ...
    STS\Metrics\MetricsServiceProvider::class,
],

'aliases' => [
    ..
    'Metrics' => STS\Metrics\MetricsFacade::class,
],
```

## Configuration

### InfluxDB

Add the following to your `.env` file:

```
METRICS_BACKEND=influxdb
IDB_USERNAME=...
IDB_PASSWORD=...
IDB_HOST=...
IDB_DATABASE=...

# Only if you are not using the default 8086
IDB_TCP_PORT=...

# If you want to send metrics over UDP instead of TCP
IDB_UDP_PORT=...
```

### CloudWatch

First make sure you have AWS itself properly setup. That means installing `aws/aws-sdk-php` and making sure you have a valid `AWS_ACCESS_KEY_ID` and `AWS_SECRET_ACCESS_KEY` in your .env file.
 
From there, you simple need to add:

```
METRICS_BACKEND=influxdb
CLOUDWATCH_NAMESPACE=..
```

## Sending an individual metric

You can create metric by using the facade like this:

```php
Metrics::create('order_placed')
    ->setValue(1)
    ->setTags([
        'source' => 'email-campaign',
        'user' => 54
    ])
    ->setExtra([
        'total' => 125
    ])
    ->setTimestamp(Carbon::yesterday());
```

The only required attribute is the `name`, everything else is optional. 

The `extra` array will be mapped to fields in InfluxDB.

## Automatically sending metrics from Laravel events

This is where things really get fun! Let's say you have a simple Laravel event called OrderPlaced:

```php
class OrderPlaced {
    protected $order;
    
    public function __construct($order)
    {
        $this->order = $order;
    }
}
```

To automatically generate a metric on this event, you just need to implement an interface and add a trait like this:
 
```php
use STS\Metrics\Contracts\ShouldReportMetric;
use STS\Metrics\Traits\ProvidesMetric;

class OrderPlaced implements ShouldReportMetric {
    use ProvidesMetric;
    
    protected $order;
    
    public function __construct($order)
    {
        $this->order = $order;
    }
}
```

That's it. Anytime this event is dispatched, a simple metric with the name `order_placed` will be created.

## Customizing event metric data

Of course, you likely will want to customize the event metric data. 

You can provide metric data with class attributes:

```php
class OrderPlaced implements ShouldReportMetric {
    use ProvidesMetric;
    
    protected $metricName = "new_order";
    protected $metricValue = 1;
    ...
```

Or if some of your metric data is dynamic you can use getter methods:

```php
public function getMetricValue()
{
    return $this->order->total;
}
```

You can provide the metric name, value, tags, extra, or timestamp using these class attributes or getter methods. 
