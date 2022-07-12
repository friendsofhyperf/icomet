<?php

declare(strict_types=1);
/**
 * This file is part of icomet.
 *
 * @link     https://github.com/friendsofhyperf/icomet
 * @document https://github.com/friendsofhyperf/icomet/blob/1.x/README.md
 * @contact  huangdijia@gmail.com
 */
return [
    'uri' => env('ICOMET_URI', 'http://127.0.0.1:8000'),
    'timeout' => (int) env('ICOMET_TIMEOUT', 5),
    'concurrent' => [
        'limit' => (int) env('ICOMET_CONCURRENT_LIMIT', 64),
    ],
];
