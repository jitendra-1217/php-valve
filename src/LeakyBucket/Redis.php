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

    public function attempt(string $resource, int $worth = 1): Response
    {
        $args = [
            file_get_contents(__DIR__ . '/redis.lua'),
            2,
            "{$this->prefix}{$resource}:t",
            "{$this->prefix}{$resource}:s",
            $this->maxBucketSize,
            $this->leakRateValue,
            $this->leakRateDuration,
            $this->getLeakFullTime(),
            time(),
            $worth,
        ];

        $response = $this->redis->eval(...$args);

        return new Response(...$response);
    }
}
