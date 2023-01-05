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
        defined('BASE_PATH') || define('BASE_PATH', dirname(__DIR__, 2) . DIRECTORY_SEPARATOR);

        return [
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
