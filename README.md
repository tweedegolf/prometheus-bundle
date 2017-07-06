# TweedeGolfPrometheusBundle
A Symfony bundle for the [tweede golf prometheus client]. For more information
on Prometheus you can [check their website][prometheus].

## Installation and configuration
Using [Composer] add the bundle to your dependencies using the require command:

    composer require tweedegolf/prometheus-bundle

### Add the bundle to your AppKernel
Add the bundle in your `app/AppKernel.php`:

```php
public function registerBundles()
{
    return array(
        // ...
        new TweedeGolf\PrometheusBundle\TweedeGolfPrometheusBundle(),
        // ...
    );
}
```

### Configure storage, collectors and routes
To allow prometheus to scrape your metrics from your application, make sure you
make a route available for the prometheus metrics controller:

```yaml
tweede_golf_prometheus:
    resource: "@TweedeGolfPrometheusBundle/Resources/config/routing.yml"
    prefix: /
```

You can also implement your own controller, take a look at the source code of
`TweedeGolf\PrometheusBundle\Controller\MetricsController::metricsAction`.
You can configure some aspects of the prometheus client using the configuration,
the default values are shown below:

```yaml
tweede_golf_prometheus:
    storage_adapter_service: TweedeGolf\PrometheusClient\Storage\ApcuAdapter
    metrics_path: /metrics
    collectors: ~
```

To adjust, create a section `tweede_golf_prometheus` in your `config.yml`. You
may specify any number of collectors. An example where four different collectors
are defined is shown below:

```yaml
tweede_golf_prometheus:
    collectors:
        requests:
            counter:
                labels: [url]
                help: Number of requests
        throughput:
            gauge:
                labels: [url]
                help: Throughput per url
                initializer: 10.0
        response_timing:
            histogram:
                labels: [url]
                help: Response timings
                buckets: [0.1, 0.2, 0.3, 0.5, 0.7, 1, 2, 5, 10, 30, 60]
        shorthand_example:
            counter: ~
```

### Modifying (incrementing/observing/setting) metrics
To modify a metric, retrieve it via the `CollectorRegistry` service and call
one of the type specific metric modification methods.

```php
use TweedeGolf\PrometheusClient\CollectorRegistry;

public function exampleAction()
{
    $metric = $this->get(CollectorRegistry::class)->getCounter('requests');
    $metric->inc();
}
```

### Register a collector service
You can also register services as collectors with the collector registry. To
do this, add a `tweede_golf_prometheus.collector` tag to your service and make
sure the service implements the `CollectorInterface`. You can also use the
factory methods of the registry service:

```yaml
services:
    example.collector.test:
        class: TweedeGolf\PrometheusClient\Collector\Counter
        factory: TweedeGolf\PrometheusClient\CollectorRegistry:createCounter
        arguments: [test]
        tags: [tweede_golf_prometheus.collector]
```

[tweede golf prometheus client]: https://github.com/tweedegolf/prometheus-client
[prometheus]: https://prometheus.io
[Composer]: https://getcomposer.org
