<?php

namespace Oro\Bundle\CyberSourceBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class OroCyberSourceExtension extends Extension
{
    const ALIAS = 'oro_cybersource';

    /**
     * @param array $configs
     * @param ContainerBuilder $container
     *
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
        $loader->load('integration.yml');
        $loader->load('form_types.yml');
        $loader->load('method.yml');
        $loader->load('listeners.yml');
        $loader->load('payment_action.yml');
    }

    /**
     * @return string
     */
    public function getAlias()
    {
        return static::ALIAS;
    }
}
