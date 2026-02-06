<?php

namespace Omobude\DhlBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class OmobudeDhlExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        // Make config available to services
        $container->setParameter('omobude_dhl.api_key', $config['api_key']);
        $container->setParameter('omobude_dhl.api_secret', $config['api_secret']);
        $container->setParameter('omobude_dhl.account_number', $config['account_number']);
        $container->setParameter('omobude_dhl.sandbox', $config['sandbox']);
        $container->setParameter('omobude_dhl.api_url', $config['api_url']);

        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__.'/../../config')
        );
        $loader->load('services.yaml');
    }
}
