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

    /**
     * Sign.
     * @return array
     */
    public function sign(string $cname, int $expires = 60)
    {
        $response = $this->client->get('/sign', compact('cname', 'expires'));

        return Response::make($response)->json();
    }

    /**
     * Push.
     * @param array|string $content
     * @return bool
     */
    public function push(string $cname, $content)
    {
        if (is_array($content)) {
            $content = json_encode($content, JSON_UNESCAPED_UNICODE);
        }

        $response = $this->client->get('/push', compact('cname', 'content'));

        return Response::make($response)->json('type') == 'ok';
    }

    /**
     * Broadcast.
     * @param array|string $content
     * @param null|string|string[] $cnames
     * @return array|bool
     */
    public function broadcast($content, $cnames = null)
    {
        if (is_array($content)) {
            $content = json_encode($content, JSON_UNESCAPED_UNICODE);
        }

        if (is_null($cnames)) {
            $response = $this->client->get('/broadcast', compact('content'));

            return Response::make($response)->body() == 'ok';
        }

        $callbacks = [];

        foreach ((array) $cnames as $cname) {
            $callbacks[] = function () use ($cname, $content) {
                $response = $this->client->get('/broadcast', compact('cname', 'content'));

                return Response::make($response)->body() == 'ok';
            };
        }

        return parallel($callbacks, (int) $this->config->get('concurrent.limit', 64));
    }

    /**
     * Check.
     * @return bool
     */
    public function check(string $cname)
    {
        $response = $this->client->get('/check', compact('cname'));

        return isset(Response::make($response)->json()[$cname]);
    }

    /**
     * Close.
     *
     * @return bool
     */
    public function close(string $cname)
    {
        $response = $this->client->get('/close', compact('cname'));

        return substr(Response::make($response)->body(), 0, 2) == 'ok';
    }

    /**
     * Clear.
     * @return bool
     */
    public function clear(string $cname)
    {
        $response = $this->client->get('/clear', compact('cname'));

        return substr(Response::make($response)->body(), 0, 2) == 'ok';
    }

    /**
     * Info.
     * @return array
     */
    public function info(string $cname = '')
    {
        $response = $this->client->get('/info', $cname ? compact('cname') : []);

        return Response::make($response)->json();
    }

    /**
     * Psub.
     */
    public function psub(Closure $callback)
    {
        $url = rtrim($this->config->get('uri'), '/') . '/psub';
        $handle = fopen($url, 'rb');

        if ($handle === false) {
            throw new \RuntimeException('Cannot open ' . $url);
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
