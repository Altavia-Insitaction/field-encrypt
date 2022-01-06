<?php

namespace Insitaction\FieldEncryptBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('field_encrypt');
        $treeBuilder->getRootNode()
            ->children()
                ->scalarNode('encrypt_key')
                    ->defaultValue('%env(ENCRYPT_KEY)%')
                    ->info('aes-256-cbc key.')
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
