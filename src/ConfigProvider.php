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

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            // 'annotations' => [
            //     'scan' => [
            //         'paths' => [
            //             __DIR__,
            //         ],
            //     ],
            // ],
            'dependencies' => [
                ClientInterface::class => ClientFactory::class,
            ],
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'The config file of friendsofhyperf/icomet.',
                    'source' => __DIR__ . '/../publish/icomet.php',
                    'destination' => BASE_PATH . '/config/autoload/icomet.php',
                ],
            ],
        ];
    }
}
