<?php
declare(strict_types=1);

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
        $container->setParameter('omobude_dhl.client_id', $config['client_id']);
        $container->setParameter('omobude_dhl.client_secret', $config['client_secret']);
        $container->setParameter('omobude_dhl.sandbox', $config['sandbox']);

        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__.'/../../config')
        );
        $loader->load('services.yaml');
    }
}
