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
     * @param array|string $content
     */
    public function push($cname, $content): bool;

    /**
     * Broadcast.
     * @param array|string $content
     * @param null|string|string[] $cnames
     */
    public function broadcast($content, $cnames = null): bool;

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
