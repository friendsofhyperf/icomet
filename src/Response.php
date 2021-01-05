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

use Psr\Http\Message\ResponseInterface;
use RuntimeException;

class Response
{
    /**
     * @var ResponseInterface
     */
    private $response;

    /**
     * @var null|array
     */
    private $decoded;

    public function __construct(ResponseInterface $response)
    {
        $this->response = $response;
    }

    /**
     * @return Response
     */
    public static function make(ResponseInterface $response)
    {
        return new self($response);
    }

    /**
     * @throws RuntimeException
     * @return string
     */
    public function body()
    {
        return $this->response->getBody()->getContents();
    }

    /**
     * @param null|mixed $default
     * @throws RuntimeException
     * @return mixed
     */
    public function json(string $key = '', $default = null)
    {
        if (is_null($this->decoded)) {
            $this->decoded = json_decode($this->body(), true);
        }

        if ($key == '') {
            return $this->decoded;
        }

        return data_get($this->decoded, $key, $default);
    }
}
