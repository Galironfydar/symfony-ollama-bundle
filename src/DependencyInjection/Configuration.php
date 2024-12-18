<?php

namespace Galironfydar\OllamaBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('ollama');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->scalarNode('base_url')
                    ->defaultValue('http://localhost:11434')
                    ->info('The base URL of your Ollama instance')
                ->end()
            ->end();

        return $treeBuilder;
    }
} 