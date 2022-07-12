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

interface ClientInterface
{
    /**
     * Sign.
     * @param string $cname
     */
    public function sign($cname, int $expires = 60): array;

    /**
     * Push.
     * @param string $cname
     */
    public function push($cname, array|string $content): bool;

    /**
     * Broadcast.
     * @param null|string|string[] $cnames
     */
    public function broadcast(array|string $content, $cnames = null): bool;

    /**
     * Check.
     * @param string $cname
     */
    public function check($cname): bool;

    /**
     * Close.
     * @param string $cname
     */
    public function close($cname): bool;

    /**
     * Clear.
     * @param string $cname
     */
    public function clear($cname): bool;

    /**
     * Info.
     * @param string $cname
     */
    public function info($cname): array;

    /**
     * Psub.
     */
    public function psub(callable $callback): void;
}
