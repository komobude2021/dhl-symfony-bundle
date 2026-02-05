<?php

namespace Omobude\DhlBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('omobude_dhl');

        $treeBuilder->getRootNode()
            ->children()
            ->scalarNode('api_key')
            ->isRequired()
            ->cannotBeEmpty()
            ->end()
            ->scalarNode('api_secret')
            ->isRequired()
            ->cannotBeEmpty()
            ->end()
            ->scalarNode('account_number')
            ->isRequired()
            ->cannotBeEmpty()
            ->end()
            ->booleanNode('sandbox')
            ->defaultTrue()
            ->end()
            ->scalarNode('api_url')
            ->defaultValue('https://api-sandbox.dhl.com')
            ->end()
            ->end();

        return $treeBuilder;
    }
}
