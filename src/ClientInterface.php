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
     * @return array{type:string,cname:string,seq:int,token:string,expires:int, sub_timeout:int}
     */
    public function sign(string $cname, int $expires = 60): array;

    /**
     * Push.
     */
    public function push(string $cname, array|string $content): bool;

    /**
     * Broadcast.
     * @return array{type:string}
     */
    public function broadcast(array|string $content, null|array|string $cnames = null): bool;

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
     * @return array{?version:string,?channels:int,subscribers:int,cname:?string}
     */
    public function info(string $cname): array;

    /**
     * Psub.
     */
    public function psub(callable $callback): void;
}
