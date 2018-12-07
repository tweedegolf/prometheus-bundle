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
            new Reference($config['storage_adapter_service']),
            $config['make_memory_adapter'],
            $config['register_defaults'],
        ]);
        $container->setDefinition(CollectorRegistry::class, $registry);

        foreach ($config['collectors'] as $name => $collector) {
            if (isset($collector['counter']) && $collector['counter']['active']) {
                $registry->addMethodCall('createCounter', [
                    $name,
                    $collector['counter']['labels'],
                    $collector['counter']['help'],
                    $collector['counter']['storage'],
                    true,
                ]);
            } elseif (isset($collector['gauge']) && $collector['gauge']['active']) {
                $registry->addMethodCall('createGauge', [
                    $name,
                    $collector['gauge']['labels'],
                    $this->makeInitializer($collector['gauge']['initializer'], $container),
                    $collector['gauge']['help'],
                    $collector['gauge']['storage'],
                    true,
                ]);
            } elseif (isset($collector['histogram']) && $collector['histogram']['active']) {
                $buckets = count($collector['histogram']['buckets']) === 0 ? null : $collector['histogram']['buckets'];

                $registry->addMethodCall('createHistogram', [
                    $name,
                    $collector['histogram']['labels'],
                    $buckets,
                    $collector['histogram']['help'],
                    $collector['histogram']['storage'],
                    true,
                ]);
            } else {
                throw new \InvalidArgumentException("Collector without any active type found");
            }
        }

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
    }

    /**
     * @param mixed $init
     * @param ContainerBuilder $container
     * @return callable|float
     */
     private function makeInitializer($init, ContainerBuilder $container)
     {
         if ($init !== null) {
             if (is_float($init) || is_int($init)) {
                 return $init;
             } elseif (is_array($init) && count($init) === 2 && substr($init[0], 0, 1) === '@') {
                 $ref = new Reference(substr($init[0], 1));
                 return [$ref, $init[1]];
             } elseif (is_string($init)) {
                 $init = $container->getParameterBag()->resolveValue($init);
                 if (is_numeric($init)) {
                     return floatval($init);
                 } elseif (is_callable($init, false)) {
                     return $init;
                 } else {
                     throw new \RuntimeException("Cannot use string as default value");
                 }
             } else {
                 throw new \RuntimeException("Unknown type for initializer");
             }
         }

         return null;
     }
}
