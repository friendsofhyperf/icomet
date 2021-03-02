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
use Hyperf\Guzzle\ClientFactory;
use RuntimeException;

class Client implements ClientInterface
{
    /**
     * @var \GuzzleHttp\Client
     */
    protected $client;

    /**
     * @var ConfigInterface
     */
    protected $config;

    public function __construct(ConfigInterface $config, ClientFactory $clientFactory)
    {
        $this->config = $config;
        $this->client = $clientFactory->create([
            'base_uri' => $config->get('uri'),
            'timeout' => (int) $config->get('timeout', 5),
        ]);
    }

    public function sign(string $cname, int $expires = 60)
    {
        $response = $this->client->request('GET', '/sign', ['query' => compact('cname', 'expires')]);

        return Response::make($response)->json();
    }

    public function push(string $cname, $content)
    {
        if (is_array($content)) {
            $content = json_encode($content, JSON_UNESCAPED_UNICODE);
        }

        $response = $this->client->request('GET', '/push', ['query' => compact('cname', 'content')]);

        return Response::make($response)->json('type') == 'ok';
    }

    public function broadcast($content, $cnames = null)
    {
        if (is_array($content)) {
            $content = json_encode($content, JSON_UNESCAPED_UNICODE);
        }

        if (is_null($cnames)) {
            $response = $this->client->request('GET', '/broadcast', ['query' => compact('content')]);

            return Response::make($response)->body() == 'ok';
        }

        $callbacks = [];

        foreach ((array) $cnames as $cname) {
            $callbacks[] = function () use ($cname, $content) {
                $response = $this->client->request('GET', '/broadcast', ['query' => compact('cname', 'content')]);

                return Response::make($response)->body() == 'ok';
            };
        }

        return parallel($callbacks, (int) $this->config->get('concurrent.limit', 64));
    }

    public function check(string $cname)
    {
        $response = $this->client->request('GET', '/check', ['query' => compact('cname')]);

        return isset(Response::make($response)->json()[$cname]);
    }

    public function close(string $cname)
    {
        $response = $this->client->request('GET', '/close', ['query' => compact('cname')]);

        return substr(Response::make($response)->body(), 0, 2) == 'ok';
    }

    public function clear(string $cname)
    {
        $response = $this->client->request('GET', '/clear', ['query' => compact('cname')]);

        return substr(Response::make($response)->body(), 0, 2) == 'ok';
    }

    public function info(string $cname = '')
    {
        $response = $this->client->request('GET', '/info', ['query' => $cname ? compact('cname') : []]);

        return Response::make($response)->json();
    }

    public function psub(Closure $callback)
    {
        $url = rtrim($this->config->get('uri'), '/') . '/psub';
        $handle = fopen($url, 'rb');

        if ($handle === false) {
            throw new RuntimeException('Cannot open ' . $url);
        }

        while (! feof($handle)) {
            $line = fread($handle, 8192);
            $line = trim($line);

            if (empty($line)) {
                continue;
            }

            $data = explode(' ', $line, 2);
            $status = (int) ($data[0] ?? 0);
            $channel = (int) ($data[1] ?? 0);

            $callback($channel, $status);
        }

        fclose($handle);
    }
}
