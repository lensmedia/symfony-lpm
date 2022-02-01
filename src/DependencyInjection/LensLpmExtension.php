<?php

namespace Lens\Bundle\LpmBundle\DependencyInjection;

use Lens\Bundle\LpmBundle\LpmHttpClient;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpClient\ScopingHttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class LensLpmExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new PhpFileLoader($container, new FileLocator(__DIR__.'/../Resources'));
        $loader->load('Services.php');

        $container->register('lens_lpm.http_client', ScopingHttpClient::class)
            ->setFactory([ScopingHttpClient::class, 'forBaseUri'])
            ->setArguments([
                new Reference(HttpClientInterface::class),
                $config['root'],
                [
                    'auth_basic' => [$config['username'], $config['password']]
                ]
            ])
            ->addTag('http_client.client')
        ;

        $container->getDefinition(LpmHttpClient::class)
            ->setArgument(0, $container->getDefinition('lens_lpm.http_client'));
    }
}
