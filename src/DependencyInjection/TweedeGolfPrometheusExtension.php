<?php

namespace TweedeGolf\PrometheusBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use TweedeGolf\PrometheusClient\CollectorRegistry;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @link http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class TweedeGolfPrometheusExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('tweede_golf_prometheus.metrics_path', $config['metrics_path']);
        $registry = new Definition(CollectorRegistry::class, [
            new Reference($config['storage_adapter_service'])
        ]);
        $container->setDefinition(CollectorRegistry::class, $registry);

        foreach ($config['collectors'] as $name => $collector) {
            if (isset($collector['counter']) && $collector['counter']['active']) {
                $registry->addMethodCall('createCounter', [
                    $name,
                    $collector['counter']['labels'],
                    $collector['counter']['help'],
                    true,
                ]);
            } elseif (isset($collector['gauge']) && $collector['gauge']['active']) {
                $registry->addMethodCall('createGauge', [
                    $name,
                    $collector['gauge']['labels'],
                    $collector['gauge']['initializer'],
                    $collector['gauge']['help'],
                    true,
                ]);
            } elseif (isset($collector['histogram']) && $collector['histogram']['active']) {
                $buckets = count($collector['histogram']['buckets']) === 0 ? null : $collector['histogram']['buckets'];

                $registry->addMethodCall('createHistogram', [
                    $name,
                    $collector['histogram']['labels'],
                    $buckets,
                    $collector['histogram']['help'],
                    true,
                ]);
            } else {
                throw new \InvalidArgumentException("Collector without any active type found");
            }
        }

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
    }
}
