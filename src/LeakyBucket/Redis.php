<?php

namespace Jitendra\PhpValve\LeakyBucket;

use Jitendra\PhpValve\Base\Response;

class Redis extends Base
{
    /**
     * @var \Predis\Client
     */
    protected $redis;

    public function __construct(int $maxBucketSize, int $leakRateValue, int $leakRateDuration, \Predis\Client $redis = null)
    {
        parent::__construct($maxBucketSize, $leakRateValue, $leakRateDuration);

        $this->redis = $redis ?: new \Predis\Client;
    }

    public function attempt(string $resource, int $cost = 1): Response
    {
        $args = [
            file_get_contents(__DIR__ . '/redis.lua'),
            1,
            $this->prefix . $resource,
            $this->maxBucketSize,
            $this->leakRateValue,
            $this->leakRateDuration,
            $this->getLeakFullTime(),
            time(),
            $cost,
        ];

        $response = $this->redis->eval(...$args);
        // Lua's boolean get type-casted in redis returned value: True -> True, False -> Nil. :(
        // Ref: https://redis.io/commands/eval#conversion-between-lua-and-redis-data-types
        if (is_null($response[0])) { $response[0] = false; }

        return new Response(...$response);
    }
}
