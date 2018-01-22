<?php

namespace Jitendra\PhpValve\LeakyBucket;

final class Redis extends Base
{
    const KEY_PREFIX = 'JPVLR:';

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
            $this->leakFullTime(),
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
            'local leakFullTime           = tonumber(ARGV[4]) ' .
            'local now                    = tonumber(ARGV[5]) ' .
            'local worth                  = tonumber(ARGV[6]) ' .

            'local ttlSecs                = math.ceil(leakFullTime/1000) ' .
            'local retryAfter             = -1 ' .

            // Reads values of 2 keys - last updated and current bucket size against key
            'local resourceLastUpdated    = tonumber(redis.call(\'GET\', resourceLastUpdatedKey)) if resourceLastUpdated == nil then resourceLastUpdated = 0 end ' .
            'local resourceBucketSize     = tonumber(redis.call(\'GET\', resourceBucketSizeKey)) if resourceBucketSize == nil then resourceBucketSize = 0 end ' .

            // Calculates units that should have been leaked between time the key got last updated and now
            'local leak                   = math.floor((now - resourceLastUpdated) * leakRateValue / leakRateDuration) ' .
            'resourceBucketSize           = math.max(0, resourceBucketSize - leak) ' .
            'resourceLastUpdated          = now ' .

            // Determine now if attempt for worth should be allowed or not. If allowed fill in worth unit in the bucket
            // and change related vars accordingly
            'local allowAttempt = resourceBucketSize + worth <= maxBucketSize ' .
            'if allowAttempt then resourceBucketSize = resourceBucketSize + worth allowAttempt = 1 else allowAttempt = 0 retryAfter = now + leakRateDuration end ' .

            // Finally set the values and TTL for the 2 REDIS keys
            'redis.call(\'SETEX\', resourceLastUpdatedKey, ttlSecs, resourceLastUpdated) ' .
            'redis.call(\'SETEX\', resourceBucketSizeKey, ttlSecs, resourceBucketSize) ' .

            'return { allowAttempt, maxBucketSize, maxBucketSize - resourceBucketSize, now + leakFullTime, retryAfter }';
    }

    public function resourceLastUpdatedKey(string $resource): string
    {
        return self::KEY_PREFIX . $resource . ':t';
    }

    public function resourceBucketSizeKey(string $resource): string
    {
        return self::KEY_PREFIX . $resource . ':s';
    }
}
