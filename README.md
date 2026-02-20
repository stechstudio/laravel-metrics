# Laravel Metrics

[![Latest Version on Packagist](https://img.shields.io/packagist/v/stechstudio/laravel-metrics.svg?style=flat-square)](https://packagist.org/packages/stechstudio/laravel-metrics)
[![Total Downloads](https://img.shields.io/packagist/dt/stechstudio/laravel-metrics.svg?style=flat-square)](https://packagist.org/packages/stechstudio/laravel-metrics)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)

This package makes it incredibly easy to ship app metrics to backends such as PostHog, InfluxDB or CloudWatch.

There are two major components: a facade that lets you create metrics on your own, and an event listener to
automatically send metrics for Laravel events.

## Installation

You know the drill...

```
composer require stechstudio/laravel-metrics
```

## Backend configuration

### PostHog

1. Install the PostHog PHP client: `composer require posthog/posthog-php`

2. Add the following to your `.env` file:

```
METRICS_BACKEND=posthog
POSTHOG_API_KEY=...
```

### InfluxDB v1.7 and under

1. Install the InfluxDB PHP client: `composer require influxdb/influxdb-php`

2. Add the following to your `.env` file:

```
METRICS_BACKEND=influxdb
IDB_USERNAME=...
IDB_PASSWORD=...
IDB_HOST=...
IDB_DATABASE=...
IDB_VERSION=1 # Default

# Only if you are not using the default 8086
IDB_TCP_PORT=...

# If you want to send metrics over UDP instead of TCP
IDB_UDP_PORT=...
```

### InfluxDB V1.8 and above

1. Install the InfluxDB PHP client: `composer require influxdata/influxdb-client-php`

2. Add the following to your `.env` file:

3. In order to use UDP with InfluxDB V1.8+ you must follow
extra [setup steps](https://github.com/influxdata/influxdb-client-php#writing-via-udp)

Add the following to your `.env` file:

```
METRICS_BACKEND=influxdb
IDB_TOKEN=...
IDB_DATABASE=... # Use the name of your desired bucket for this value
IDB_HOST=...
IDB_ORG=...
IDB_VERSION=2

# Only if you are not using the default 8086
IDB_TCP_PORT=...

# If you want to send metrics over UDP instead of TCP
IDB_UDP_PORT=...
```

### CloudWatch

1. Install the AWS PHP SDK: `composer require aws/aws-sdk-php`.

2. Add the following to your `.env` file:

```
METRICS_BACKEND=cloudwatch
CLOUDWATCH_NAMESPACE=...

AWS_DEFAULT_REGION=...
AWS_ACCESS_KEY_ID=...
AWS_SECRET_ACCESS_KEY=...
```

### Prometheus
1. Install the Prometheus PHP client: `composer require promphp/prometheus_client_php`
2. Configuring the backend to use Prometheus, makes sense only if you have an endpoint to expose them.
   Its purpose is only to format the registered metrics in a way that Prometheus can scrape them.

```
METRICS_BACKEND=prometheus
```

### NullDriver (for development)

If you need to disable metrics just set the backend to null:

```
METRICS_BACKEND=null
```

This `null` driver will simply discard any metrics.

## Sending an individual metric

You can create metric by using the facade like this:

```php
Metrics::create('order_placed')
    ->setValue(1)
    ->setTags([
        'source' => 'email-campaign',
        'user' => 54
    ]);
```

The only required attribute is the `name`, everything else is optional.

## Driver mapping

This is how we are mapping metric attributes in our backends.

| Metric attribute | PostHog           | InfluxDB      | CloudWatch        | Prometheus                                    |
|------------------|-------------------|---------------|-------------------|-----------------------------------------------|
| name             | event             | measurement   | MetricName        | name                                          |
| value            | properties[value] | fields[value] | Value             | value                                         |
| unit             | _ignored_         | _ignored_     | Unit              | _ignored_                                     |
| resolution       | _ignored_         | _ignored_     | StorageResolution | _ignored_                                     |
| tags             | _ignored_         | tags          | Dimensions        | keys -> labelNames<br/> values -> labelValues |
| extra            | properties        | fields        | _ignored_         | _ignored_                                     |
| timestamp        | _ignored_         | timestamp     | Timestamp         | _ignored_                                     |
| description      | _ignored_         | _ignored_     | _ignored_         | help                                          |
| namespace        | _ignored_         | _ignored_     | _ignored_         | namespace                                     |
| type             | _ignored_         | _ignored_     | _ignored_         | used to register counter or gauge metric      |

See the [CloudWatch docs](http://docs.aws.amazon.com/AmazonCloudWatch/latest/APIReference/API_MetricDatum.html)
and [InfluxDB docs](https://docs.influxdata.com/influxdb/latest/concepts/key_concepts/) for more information on their
respective data formats. Note we only do minimal validation, you are expected to know what data types and formats your
backend supports for a given metric attribute.

## Sending metrics from Laravel events

The main motivation for this library was to send metrics automatically when certain events occur in a Laravel
application. So this is where things really get fun!

Let's say you have a simple Laravel event called OrderReceived:

```php
class OrderReceived {
    protected $order;
    
    public function __construct($order)
    {
        $this->order = $order;
    }
}
```

The first step is to implement an interface:

```php
use STS\Metrics\Contracts\ShouldReportMetric;

class OrderReceived implements ShouldReportMetric {
```

This will tell the global event listener to send a metric for this event.

There are two different ways you can then provide the metric details.

### 1. Use the `ProvidesMetric` trait

You can also include a trait that helps with building this metric:

```php
use STS\Metrics\Contracts\ShouldReportMetric;
use STS\Metrics\Traits\ProvidesMetric;

class OrderReceived implements ShouldReportMetric {
    use ProvidesMetric;
```

In this case, the trait will build a metric called `order_received` (taken from the class name) with a value of `1`.

#### Customizing event metric data

If you decide to use the trait, you likely will want to customize the event metric data.

You can provide metric data with class attributes:

```php
class OrderReceived implements ShouldReportMetric {
    use ProvidesMetric;
    
    protected $metricName = "new_order";
    protected $metricTags = ["category" => "revenue"];
    ...
```

Or if some of your metric data is dynamic you can use getter methods:

```php
public function getMetricValue()
{
    return $this->order->total;
}
```

You can provide any of our metric attributes using these class attributes or getter methods.

### 2. Create the metric yourself

Depending on how much detail you need to provide for your metric, it may be simpler to just build it yourself. In this
case you can ditch the trait and simply provide a public `createMetric` function that returns a new `Metric` instance:

```php
use STS\Metrics\Contracts\ShouldReportMetric;
use STS\Metrics\Metric;

class OrderReceived implements ShouldReportMetric {
    protected $order;
    
    public function __construct($order)
    {
        $this->order = $order;
    }
    
    public function createMetric()
    {
        return (new Metric('order_received'))
            ->setValue(...)
            ->setTags([...])
            ->setTimestamp(...)
            ->setResolutions(...);
    }
}
```

## Default tags and extra data

You can set default tags and extra data on the driver that will be merged into every metric:

```php
Metrics::setTags(['environment' => 'production']);
Metrics::setExtra(['server' => 'web-01']);
```

### Dynamic extra data with closures

If you need extra data that is evaluated at the time each metric is dispatched (rather than when it's initially set), you can pass a closure:

```php
Metrics::setExtra(fn() => [
    'memory' => memory_get_usage(),
    'cpu' => sys_getloadavg()[0],
]);
```

The closure will be called fresh each time a metric is formatted. This also works on individual metrics:

```php
(new Metric('my_metric'))
    ->setExtra(fn() => ['memory' => memory_get_usage()])
    ->add();
```

## User ID resolution

Drivers automatically resolve the current user ID via `getUserId()`:

1. If a user is authenticated: `auth()->id()`
2. Otherwise: `getAnonymousId()`, which returns a hashed session ID if a session is active, or a random string (stable for the lifetime of the process)

This is used by the PostHog driver as the `distinctId`, and is available to any driver via `$driver->getUserId()`.

### Custom user ID resolver

You can override the default resolution by providing your own closure. The driver instance is passed to your closure, so you can fall back to the built-in anonymous ID if needed:

```php
Metrics::resolveUserIdUsing(function($driver) {
    return auth()->check()
        ? 'custom-prefix:' . auth()->id()
        : $driver->getAnonymousId();
});
```

This will apply to all drivers. You can also set it on a specific driver:

```php
Metrics::driver('posthog')->resolveUserIdUsing(fn() => $team->id);
```
