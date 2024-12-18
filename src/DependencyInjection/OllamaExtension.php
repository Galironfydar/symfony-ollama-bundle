<?php

namespace Galironfydar\OllamaBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class OllamaExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        // Load the services.yaml file
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__.'/../Resources/config')
        );
        $loader->load('services.yaml');

        // Set all configuration parameters with ollama prefix
        foreach ($config as $key => $value) {
            $container->setParameter('ollama.' . $key, $value);
        }
    }

    public function getAlias(): string
    {
        return 'ollama';
    }
} 