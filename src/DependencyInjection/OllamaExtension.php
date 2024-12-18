<?php

namespace Galironfydar\OllamaBundle\DependencyInjection;

use Galironfydar\OllamaBundle\Service\OllamaService;
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

        $container->register('ollama.service', OllamaService::class)
            ->setAutowired(true)
            ->setAutoconfigured(true)
            ->setArgument('$baseUrl', $config['base_url']);
    }
} 