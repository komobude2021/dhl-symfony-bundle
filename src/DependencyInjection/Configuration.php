<?php
declare(strict_types=1);

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
                ->scalarNode('client_id')
                    ->defaultValue('')
                    ->info('DHL API Client ID for OAuth authentication')
                ->end()
                ->scalarNode('client_secret')
                    ->defaultValue('')
                    ->info('DHL API Client Secret for OAuth authentication')
                ->end()
                ->booleanNode('sandbox')
                    ->defaultTrue()
                    ->info('Use DHL sandbox environment for testing')
                ->end()
            ->end();

        return $treeBuilder;
    }
}
