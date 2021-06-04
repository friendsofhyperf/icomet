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
use GuzzleHttp\Client as GuzzleHttpClient;
use Hyperf\Guzzle\ClientFactory;
use Psr\Container\ContainerInterface;
use RuntimeException;

class Client implements ClientInterface
{
    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     * @var ClientFactory
     */
    private $clientFactory;

    public function __construct(ContainerInterface $container)
    {
        $this->config = $container->get(ConfigInterface::class);
        $this->clientFactory = $container->get(ClientFactory::class);
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

        $callbacks = [];

        foreach ((array) $cnames as $cname) {
            $callbacks[] = function () use ($cname, $content) {
                $response = $this->client()->request('GET', '/broadcast', ['query' => compact('cname', 'content')]);

                return Response::make($response)->body() == 'ok';
            };
        }

        return parallel($callbacks, (int) $this->config->get('concurrent.limit', 64));
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

    protected function client(): GuzzleHttpClient
    {
        return $this->clientFactory->create([
            'base_uri' => $this->config->get('uri'),
            'timeout' => (int) $this->config->get('timeout', 5),
        ]);
    }
}
