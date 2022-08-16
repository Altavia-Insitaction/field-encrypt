<?php

namespace Insitaction\FieldEncryptBundle\DependencyInjection;

use Insitaction\FieldEncryptBundle\Doctrine\DBAL\Types\EncryptedString;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class FieldEncryptExtension extends Extension implements PrependExtensionInterface
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $config = $this->processConfiguration(new Configuration(), $configs);
        $container->setParameter('field_encrypt.encrypt_key', $config['encrypt_key']);
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yaml');
    }

    public function prepend(ContainerBuilder $container): void
    {
        $container->prependExtensionConfig('doctrine', [
            'dbal' => [
                'types' => [EncryptedString::NAME => EncryptedString::class],
            ],
        ]);
    }
}
