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
     */
    public function sign(string $cname, int $expires = 60): array;

    /**
     * Push.
     */
    public function push(string $cname, array|string $content): bool;

    /**
     * Broadcast.
     */
    public function broadcast(array|string $content, null|string|array $cnames = null): bool;

    /**
     * Check.
     */
    public function check(string $cname): bool;

    /**
     * Close.
     */
    public function close(string $cname): bool;

    /**
     * Clear.
     */
    public function clear(string $cname): bool;

    /**
     * Info.
     */
    public function info(string $cname): array;

    /**
     * Psub.
     */
    public function psub(callable $callback): void;
}
