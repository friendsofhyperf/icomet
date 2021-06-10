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
     * @return array
     */
    public function sign($cname, int $expires = 60);

    /**
     * Push.
     * @param string $cname
     * @param array|string $content
     * @return bool
     */
    public function push($cname, $content);

    /**
     * Broadcast.
     * @param array|string $content
     * @param null|string|string[] $cnames
     * @return bool
     */
    public function broadcast($content, $cnames = null);

    /**
     * Check.
     * @param string $cname
     * @return bool
     */
    public function check($cname);

    /**
     * Close.
     *
     * @param string $cname
     * @return bool
     */
    public function close($cname);

    /**
     * Clear.
     * @param string $cname
     * @return bool
     */
    public function clear($cname);

    /**
     * Info.
     * @param string $cname
     * @return array
     */
    public function info($cname);

    /**
     * Psub.
     */
    public function psub(callable $callback);
}
