<?php

namespace Jitendra\PhpValve\FixedBasic;

use Jitendra\PhpValve\Base\Response;

class Redis extends Base
{
    /**
     * Redis client
     *
     * @var \Predis\Client
     */
    protected $redis;

    public function __construct(int $window, int $limit, \Predis\Client $redis = null)
    {
        parent::__construct($window, $limit);

        $this->redis = $redis ?: new \Predis\Client;
    }

    public function attempt(string $resource, int $cost = 1): Response
    {
        $args = [
            file_get_contents(__DIR__ . '/redis.lua'),
            1,
            $this->prefix . $resource,
            $this->window,
            $cost,
        ];

        list($hits, $ttl) = $this->redis->eval(...$args);

        // Following logic could easily be offloaded to LUA script below
        $allowed    = $hits <= $this->limit;
        $remaining  = max(0, $this->limit - $hits);
        $reset      = time() + $ttl;
        $retryAfter = $allowed ? -1 : $reset;

        return new Response($allowed, $this->limit, $remaining, $reset, $retryAfter);
    }
}
