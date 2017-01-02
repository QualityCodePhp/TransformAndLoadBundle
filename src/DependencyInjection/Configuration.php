<?php

namespace QualityCode\TransformAndLoadBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

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
        $rootNode = $treeBuilder->root('qltyc_tl');

        $rootNode
            ->children()
                ->arrayNode('imports')
                    ->useAttributeAsKey('import_name')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('mode')->end()
                            ->integerNode('batch_size')->defaultValue(100)->end()
                            ->scalarNode('delimiter')->defaultValue(';')->end()
                            ->scalarNode('main_entity')->isRequired()->end()
                            ->append($this->addLinkEntitiesMappingNode())
                            ->append($this->addTranformerDefinitionNode())
                            ->append($this->addFieldsNode())
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }

    /**
     * @return ArrayNodeDefinition|NodeDefinition
     */
    public function addLinkEntitiesMappingNode()
    {
        $builder = new TreeBuilder();
        $node = $builder->root('link_entities_mapping');

        $node
            ->useAttributeAsKey('entity_name')
            ->prototype('scalar')->end()
            ->end();

        return $node;
    }

    /**
     * @return ArrayNodeDefinition|NodeDefinition
     */
    public function addTranformerDefinitionNode()
    {
        $builder = new TreeBuilder();
        $node = $builder->root('transformer');

        $node
            ->useAttributeAsKey('transformer_name')
            ->prototype('scalar')->end()
            ->end();

        return $node;
    }

    /**
     * @return ArrayNodeDefinition|NodeDefinition
     */
    public function addFieldsNode()
    {
        $builder = new TreeBuilder();
        $node = $builder->root('fields');

        $node
            ->useAttributeAsKey('field_name')
            ->prototype('array')
                ->children()
                    ->scalarNode('class')->end()
                    ->booleanNode('link_entity')->defaultValue(false)->end()
                    ->scalarNode('mapped_with')->isRequired()->end()
                    ->scalarNode('transform')->end()
                ->end()
            ->end()
        ;

        return $node;
    }
}
