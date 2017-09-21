<?php

namespace TweedeGolf\PrometheusBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use TweedeGolf\PrometheusClient\CollectorRegistry;

class StorageAdapterCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->has(CollectorRegistry::class)) {
            return;
        }

        $definition = $container->findDefinition(CollectorRegistry::class);
        $taggedServices = $container->findTaggedServiceIds('tweede_golf_prometheus.storage_adapter');

        foreach ($taggedServices as $id => $tags) {
            foreach ($tags as $tag) {
                if (!isset($tag['alias'])) {
                    throw new \RuntimeException("No alias specified for prometheus storage adapter with id '{$id}'");
                }
                $definition->addMethodCall('registerStorageAdapter', [$tag['alias'], new Reference($id)]);
            }
        }
    }
}

