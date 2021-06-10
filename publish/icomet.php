<?php

declare(strict_types=1);
/**
 * This file is part of icomet.
 *
 * @link     https://github.com/friendsofhyperf/icomet
 * @document https://github.com/friendsofhyperf/icomet/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
return [
    'uri' => env('ICOMET_URI', 'http://127.0.0.1:8000'),
    'timeout' => (int) env('ICOMET_TIMEOUT', 5),
    'concurrent' => [
        'limit' => (int) env('ICOMET_CONCURRENT_LIMIT', 64),
    ],
    'pool' => [
        'max_connections' => (int) env('ICOMET_POOL_MAX_CONNECTIONS', 1024),
        'retries' => (int) env('ICOMET_POOL_RETRIES', 1),
        'delay' => (int) env('ICOMET_POOL_DELAY', 10),
    ],
];
