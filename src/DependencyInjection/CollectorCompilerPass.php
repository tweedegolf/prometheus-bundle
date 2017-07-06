<?php

namespace TweedeGolf\PrometheusBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use TweedeGolf\PrometheusClient\CollectorRegistry;

class CollectorCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->has(CollectorRegistry::class)) {
            return;
        }

        $definition = $container->findDefinition(CollectorRegistry::class);
        $taggedServices = $container->findTaggedServiceIds('tweede_golf_prometheus.collector');

        foreach ($taggedServices as $id => $tags) {
            $definition->addMethodCall('register', [new Reference($id)]);
        }
    }
}
