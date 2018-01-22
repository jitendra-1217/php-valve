<?php

namespace Jitendra\PhpValve\LeakyBucket;

final class Redis extends Base
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

    public function attempt(string $resource, int $worth = 1): array
    {
        $args = [
            $this->script(),
            2,
            $this->resourceLastUpdatedKey($resource),
            $this->resourceBucketSizeKey($resource),
            $this->maxBucketSize,
            $this->leakRateValue,
            $this->leakRateDuration,
            millitime(),
            $worth,
        ];

        return $this->redis->eval(...$args);
    }

    public function script(): string
    {
        return '' .
            'local resourceLastUpdatedKey = KEYS[1] ' .
            'local resourceBucketSizeKey  = KEYS[2] ' .
            'local maxBucketSize          = tonumber(ARGV[1]) ' .
            'local leakRateValue          = tonumber(ARGV[2]) ' .
            'local leakRateDuration       = tonumber(ARGV[3]) ' .
            'local now                    = tonumber(ARGV[4]) ' .
            'local worth                  = tonumber(ARGV[5]) ' .
            'local leakFullTime           = math.ceil((maxBucketSize * leakRateDuration)/leakRateValue) ' .
            'local ttlSecs                = math.ceil(leakFullTime/1000) ' .
            'local resourceLastUpdated    = tonumber(redis.call(\'GET\', resourceLastUpdatedKey)) if resourceLastUpdated == nil then resourceLastUpdated = 0 end ' .
            'local resourceBucketSize     = tonumber(redis.call(\'GET\', resourceBucketSizeKey)) if resourceBucketSize == nil then resourceBucketSize = 0 end ' .
            'local leak                   = math.floor((now - resourceLastUpdated) * leakRateValue / leakRateDuration) ' .
            'resourceBucketSize           = math.max(0, resourceBucketSize - leak) ' .
            'resourceLastUpdated          = now ' .

            'local allowAttempt = resourceBucketSize + worth <= maxBucketSize ' .
            'if allowAttempt then resourceBucketSize = resourceBucketSize + worth allowAttempt = 1 else allowAttempt = 0 end ' .

            'redis.call(\'SETEX\', resourceLastUpdatedKey, ttlSecs, resourceLastUpdated) ' .
            'redis.call(\'SETEX\', resourceBucketSizeKey, ttlSecs, resourceBucketSize) ' .

            'return { allowAttempt, maxBucketSize, maxBucketSize - resourceBucketSize, now + leakFullTime }';
    }

    public function resourceLastUpdatedKey(string $resource): string
    {
        return "{$resource}:t";
    }

    public function resourceBucketSizeKey(string $resource): string
    {
        return "{$resource}:s";
    }
}
