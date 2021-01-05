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

use Closure;

interface ClientInterface
{
    /**
     * Sign.
     * @return array
     */
    public function sign(string $cname, int $expires = 60);

    /**
     * Push.
     * @return bool
     */
    public function push(string $cname, string $content);

    /**
     * Broadcast.
     * @param array|string $content
     * @param null|string|string[] $cnames
     * @return bool
     */
    public function broadcast($content, $cnames = null);

    /**
     * Check.
     * @return bool
     */
    public function check(string $cname);

    /**
     * Close.
     *
     * @return bool
     */
    public function close(string $cname);

    /**
     * Clear.
     * @return bool
     */
    public function clear(string $cname);

    /**
     * Info.
     * @return array
     */
    public function info(string $cname);

    /**
     * Psub.
     */
    public function psub(Closure $callback);
}
