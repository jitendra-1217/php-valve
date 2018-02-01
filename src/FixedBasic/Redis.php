<?php

namespace Jitendra\PhpValve\FixedBasic;

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

    public function attempt(string $resource, int $worth = 1): array
    {
        $args = [
            file_get_contents(__DIR__ . '/redis.lua'),
            1,
            $this->keyPrefix . $resource,
            ceil($this->window/1000),
            $worth,
        ];

        list($hits, $ttlSecsRemaining) = $this->redis->eval(...$args);

        // Following logic could easily be offloaded to LUA script below
        $allowed    = intval($hits <= $this->limit);
        $remaining  = max(0, $this->limit - $hits);
        $reset      = millitime() + ($ttlSecsRemaining * 1000);
        $retryAfter = $allowed ? -1 : $reset;

        return [
            $allowed,
            $this->limit,
            $remaining,
            $reset,
            $retryAfter,
        ];
    }
}
