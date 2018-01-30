<?php

namespace Jitendra\PhpValve\FixedBasic;

final class Redis extends Base
{
    const KEY_PREFIX = 'pv1:';

    /**
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
            $this->script(),
            1,
            $this->resourceKey($resource),
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

    public function script(): string
    {
        return '' .
            'local resourceKey      = KEYS[1] ' .
            'local ttlSecs          = tonumber(ARGV[1]) ' .
            'local worth            = tonumber(ARGV[2]) ' .
            'local ttlSecsRemaining = ttlSecs ' .

            'local hits = tonumber(redis.call(\'INCRBY\', resourceKey, worth)) ' .
            'if hits == worth then redis.call(\'setex\', resourceKey, ttlSecs, worth) else ttlSecsRemaining = tonumber(redis.call(\'TTL\', resourceKey)) end ' .

            'return { hits, ttlSecsRemaining }';
    }

    public function resourceKey(string $resource): string
    {
        return self::KEY_PREFIX . $resource;
    }
}
