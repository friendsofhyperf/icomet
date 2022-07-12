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

use FriendsOfHyperf\Http\Client\Http;
use FriendsOfHyperf\Http\Client\PendingRequest;
use Hyperf\Utils\Coroutine\Concurrent;
use RuntimeException;

class Client implements ClientInterface
{
    /**
     * @var Concurrent
     */
    protected $concurrent;

    public function __construct(protected array $config = [])
    {
        $this->concurrent = new Concurrent((int) data_get($config, 'concurrent.limit', 128));
    }

    public function sign($cname, int $expires = 60): array
    {
        return $this->client()
            ->get('/sign', compact('cname', 'expires'))
            ->throw()
            ->json();
    }

    public function push($cname, $content): bool
    {
        if (is_array($content)) {
            $content = json_encode($content, JSON_UNESCAPED_UNICODE);
        }

        return $this->client()
            ->get('/push', compact('cname', 'content'))
            ->throw()
            ->json('type') == 'ok';
    }

    public function broadcast($content, $cnames = null): bool
    {
        if (is_array($content)) {
            $content = json_encode($content, JSON_UNESCAPED_UNICODE);
        }

        if (is_null($cnames)) {
            return $this->client()
                ->get('/broadcast', compact('content'))
                ->throw()
                ->body() == 'ok';
        }

        foreach ((array) $cnames as $cname) {
            $this->concurrent->create(function () use ($cname, $content) {
                $this->push($cname, $content);
            });
        }

        return true;
    }

    public function check($cname): bool
    {
        return with(
            $this->client()
                ->get('/check', compact('cname'))
                ->throw()
                ->json(),
            fn ($json) => isset($json[$cname])
        );
    }

    public function close($cname): bool
    {
        return with(
            $this->client()
                ->get('/close', compact('cname'))
                ->throw()
                ->body(),
            fn ($body) => substr($body, 0, 2) == 'ok'
        );
    }

    public function clear($cname): bool
    {
        return with(
            $this->client()
                ->get('/clear', compact('cname'))
                ->throw()
                ->body(),
            fn ($body) => substr($body, 0, 2) == 'ok'
        );
    }

    public function info($cname = ''): array
    {
        return $this->client()
            ->get('/info', $cname ? compact('cname') : [])
            ->throw()
            ->json();
    }

    public function psub(callable $callback): void
    {
        $url = rtrim(data_get($this->config, 'uri'), '/') . '/psub';
        $handle = fopen($url, 'rb');

        if ($handle === false) {
            throw new RuntimeException('Failed to open stream:' . $url);
        }

        while (! feof($handle)) {
            $line = fread($handle, 8192);
            $line = trim($line);

            if (empty($line)) {
                continue;
            }

            [$status, $channel] = explode(' ', $line, 2) + [0, 0];

            $callback((int) $channel, (int) $status);
        }

        fclose($handle);
    }

    protected function client(): PendingRequest
    {
        return Http::baseUrl(data_get($this->config, 'uri'))
            ->timeout((int) data_get($this->config, 'timeout', 5));
    }
}
