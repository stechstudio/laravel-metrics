# Laravel Metrics

This package makes it incredibly easy to ship app metrics to backends such as InfluxDB or CloudWatch (planned).

There are two major components: imperatively sending metrics, and automatically sending metrics for Laravel events.
   
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

Currently this package supports InfluxDB as the metrics backend, with more backends planned.

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

## Sending an individual metric

You can imperatively send a metric by using the facade like this:

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

That's it. A basic event with the name `order_place` will be sent to your default backend.

## Customizing event metric data

Of course, you likely will want to customize what is sent in the event metric data. You can provide any metric details by adding class attributes or getter methods to your event:

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