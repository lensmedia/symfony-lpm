<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Lens\Bundle\LpmBundle\LpmHttpClient;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Cache\CacheInterface;

return static function (ContainerConfigurator $container) {
    $container->services()
        ->set(LpmHttpClient::class)
        ->args([
            null,
            service(SerializerInterface::class),
            service(CacheInterface::class)
        ]);
};
