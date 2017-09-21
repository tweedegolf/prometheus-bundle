<?php

namespace TweedeGolf\PrometheusBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\DependencyInjection\Reference;
use TweedeGolf\PrometheusClient\CollectorRegistry;
use TweedeGolf\PrometheusClient\Storage\ApcuAdapter;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/configuration.html}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('tweede_golf_prometheus');

        $rootNode
            ->children()
                ->scalarNode('metrics_path')->defaultValue('/metrics')->end()
                ->scalarNode('storage_adapter_service')->defaultValue(new Reference(ApcuAdapter::class))->end()
                ->arrayNode('collectors')
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->children()
                            ->arrayNode('counter')
                                ->treatNullLike(['active' => true])
                                ->treatTrueLike(['active' => true])
                                ->treatFalseLike(['active' => false])
                                ->children()
                                    ->booleanNode('active')->defaultTrue()->end()
                                    ->arrayNode('labels')->prototype('scalar')->end()->end()
                                    ->scalarNode('help')->defaultNull()->end()
                                    ->scalarNode('storage')->defaultValue(CollectorRegistry::DEFAULT_STORAGE)->end()
                                ->end()
                            ->end()
                            ->arrayNode('gauge')
                                ->treatNullLike(['active' => true])
                                ->treatTrueLike(['active' => true])
                                ->treatFalseLike(['active' => false])
                                ->children()
                                    ->booleanNode('active')->defaultTrue()->end()
                                    ->arrayNode('labels')->prototype('scalar')->end()->end()
                                    ->scalarNode('help')->defaultNull()->end()
                                    ->scalarNode('storage')->defaultValue(CollectorRegistry::DEFAULT_STORAGE)->end()
                                    ->variableNode('initializer')->defaultNull()->end()
                                ->end()
                            ->end()
                            ->arrayNode('histogram')
                                ->treatNullLike(['active' => true])
                                ->treatTrueLike(['active' => true])
                                ->treatFalseLike(['active' => false])
                                ->children()
                                    ->booleanNode('active')->defaultTrue()->end()
                                    ->arrayNode('labels')->prototype('scalar')->end()->end()
                                    ->scalarNode('help')->defaultNull()->end()
                                    ->scalarNode('storage')->defaultValue(CollectorRegistry::DEFAULT_STORAGE)->end()
                                    ->arrayNode('buckets')->prototype('float')->end()->end()
                                ->end()
                            ->end()
                        ->end()
                        ->validate()
                        ->ifTrue(function (array $data) {
                            $alreadyActive = false;
                            foreach ($data as $item) {
                                if ($alreadyActive && $item['active']) {
                                    return true;
                                }
                                $alreadyActive = $alreadyActive || $item['active'];
                            }

                            return !$alreadyActive;
                        })
                        ->thenInvalid('A collector must have exactly one type (counter, gauge, histogram) activated')
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
