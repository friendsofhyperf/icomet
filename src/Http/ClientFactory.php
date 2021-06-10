<?php

declare(strict_types=1);
/**
 * This file is part of icomet.
 *
 * @link     https://github.com/friendsofhyperf/icomet
 * @document https://github.com/friendsofhyperf/icomet/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\IComet\Http;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Guzzle\CoroutineHandler;
use Hyperf\Guzzle\PoolHandler;
use Hyperf\Guzzle\RetryMiddleware;
use Hyperf\Utils\Arr;
use Hyperf\Utils\Coroutine;
use RuntimeException;
use TypeError;

class ClientFactory
{
    /**
     * @var array
     */
    private $config;

    public function __construct(ConfigInterface $config)
    {
        $this->config = $config->get('icomet', []);
    }

    /**
     * @throws TypeError
     * @throws RuntimeException
     */
    public function create(array $options = []): Client
    {
        $config = array_replace(['handler' => $this->makeHandlerStack()], $options);

        // Create by DI for AOP.
        return make(Client::class, ['config' => $config]);
    }

    /**
     * @throws TypeError
     * @throws RuntimeException
     */
    protected function makeHandlerStack(): ?HandlerStack
    {
        if (! Coroutine::inCoroutine()) {
            return null;
        }

        if (Arr::has($this->config, 'pool.max_connections')) {
            return $this->makePoolHandlerStack();
        }

        return $this->makeCoroutineHandlerStack();
    }

    /**
     * @throws TypeError
     * @throws RuntimeException
     */
    protected function makePoolHandlerStack(): HandlerStack
    {
        $handler = make(PoolHandler::class, [
            'option' => [
                'max_connections' => (int) Arr::get($this->config, 'pool.max_connections', 50),
            ],
        ]);

        $retry = make(RetryMiddleware::class, [
            'retries' => (int) Arr::get($this->config, 'pool.retries', 1),
            'delay' => (int) Arr::get($this->config, 'pool.delay', 10),
        ]);

        $stack = HandlerStack::create($handler);
        $stack->push($retry->getMiddleware(), 'retry');

        return $stack;
    }

    /**
     * @throws RuntimeException
     */
    protected function makeCoroutineHandlerStack(): HandlerStack
    {
        return HandlerStack::create(new CoroutineHandler());
    }
}
