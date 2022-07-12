<?php

declare(strict_types=1);
/**
 * This file is part of icomet.
 *
 * @link     https://github.com/friendsofhyperf/icomet
 * @document https://github.com/friendsofhyperf/icomet/blob/1.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\IComet;

use Hyperf\Contract\ConfigInterface;
use Psr\Container\ContainerInterface;

class ClientFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return make(Client::class, [
            'config' => $container->get(ConfigInterface::class)->get('icomet'),
        ]);
    }
}
