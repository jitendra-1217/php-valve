<?php

namespace Jitendra\PhpValve\LeakyBucket;

final class Redis extends Base
{
    // Every resource has corresponding 2 REDIS keys to hold last
    // updated time stamp and actual bucket size respectively.
    const KEY1_SUFFIX = 't';
    const KEY2_SUFFIX = 's';

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
            $resource . self::KEY1_SUFFIX,
            $resource . self::KEY2_SUFFIX,
            $this->maxBucketSize,
            $this->leakRateValue,
            $this->leakRateDuration,
            (int) round(microtime(true) * 1000),
            $worth,
        ];

        return $this->redis->eval(...$args);
    }

    private function script(): string
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
            'if allowAttempt then resourceBucketSize = resourceBucketSize + worth end ' .

            'redis.call(\'SETEX\', resourceLastUpdatedKey, ttlSecs, resourceLastUpdated) ' .
            'redis.call(\'SETEX\', resourceBucketSizeKey, ttlSecs, resourceBucketSize) ' .

            'return { allowAttempt, maxBucketSize, maxBucketSize - resourceBucketSize, now + leakFullTime }';
    }
}
