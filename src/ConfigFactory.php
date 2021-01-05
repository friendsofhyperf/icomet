<?php

declare(strict_types=1);
/**
 * This file is part of icomet.
 *
 * @link     https://github.com/friendsofhyperf/icomet
 * @document https://github.com/friendsofhyperf/icomet/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\IComet;

use Hyperf\Utils\ApplicationContext;

class ConfigFactory
{
    public function __invoke()
    {
        /** @var \Psr\Container\ContainerInterface $container */
        $container = ApplicationContext::getContainer();
        /** @var Hyperf\Contract\ConfigInterface $config */
        $config = $container->get(\Hyperf\Contract\ConfigInterface::class);

        return new Config((array) $config->get('icomet', []));
    }
}
