<?php

namespace Lens\Bundle\LpmBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('lens_lpm');

        $treeBuilder->getRootNode()
            ->children()
                ->scalarNode('root')->defaultValue('https://lpm.lensmedia.nl/api/v1/')->end()
                ->scalarNode('username')->isRequired()->end()
                ->scalarNode('password')->isRequired()->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
