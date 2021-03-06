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

use FriendsOfHyperf\IComet\Http\ClientFactory;
use FriendsOfHyperf\IComet\Http\Response;
use GuzzleHttp\Client as GuzzleHttpClient;
use Hyperf\Utils\Coroutine\Concurrent;
use Psr\Container\ContainerInterface;
use RuntimeException;

class Client implements ClientInterface
{
    /**
     * @var array
     */
    protected $config;

    /**
     * @var ClientFactory
     */
    protected $clientFactory;

    /**
     * @var Concurrent
     */
    protected $concurrent;

    public function __construct(ContainerInterface $container, array $config = [])
    {
        $this->config = $config;
        $this->clientFactory = $container->get(ClientFactory::class);
        $this->concurrent = new Concurrent((int) data_get($config, 'concurrent.limit', 128));
    }

    public function sign($cname, int $expires = 60)
    {
        $response = $this->client()->request('GET', '/sign', ['query' => compact('cname', 'expires')]);

        return Response::make($response)->json();
    }

    public function push($cname, $content)
    {
        if (is_array($content)) {
            $content = json_encode($content, JSON_UNESCAPED_UNICODE);
        }

        $response = $this->client()->request('GET', '/push', ['query' => compact('cname', 'content')]);

        return Response::make($response)->json('type') == 'ok';
    }

    public function broadcast($content, $cnames = null)
    {
        if (is_array($content)) {
            $content = json_encode($content, JSON_UNESCAPED_UNICODE);
        }

        if (is_null($cnames)) {
            $response = $this->client()->request('GET', '/broadcast', ['query' => compact('content')]);

            return Response::make($response)->body() == 'ok';
        }

        foreach ((array) $cnames as $cname) {
            $this->concurrent->create(function () use ($cname, $content) {
                $this->push($cname, $content);
            });
        }

        return true;
    }

    public function check($cname)
    {
        $response = $this->client()->request('GET', '/check', ['query' => compact('cname')]);

        return isset(Response::make($response)->json()[$cname]);
    }

    public function close($cname)
    {
        $response = $this->client()->request('GET', '/close', ['query' => compact('cname')]);

        return substr(Response::make($response)->body(), 0, 2) == 'ok';
    }

    public function clear($cname)
    {
        $response = $this->client()->request('GET', '/clear', ['query' => compact('cname')]);

        return substr(Response::make($response)->body(), 0, 2) == 'ok';
    }

    public function info($cname = '')
    {
        $response = $this->client()->request('GET', '/info', ['query' => $cname ? compact('cname') : []]);

        return Response::make($response)->json();
    }

    public function psub(callable $callback)
    {
        $url = rtrim(data_get($this->config, 'uri'), '/') . '/psub';
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

    protected function client(): GuzzleHttpClient
    {
        return $this->clientFactory->create([
            'base_uri' => data_get($this->config, 'uri'),
            'timeout' => (int) data_get($this->config, 'timeout', 5),
        ]);
    }
}
