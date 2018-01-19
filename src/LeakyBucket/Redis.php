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

    public function __construct(
        int $maxBucketSize,
        int $leakRateValue,
        int $leakRateDuration,
        \Predis\Client $redis = null)
    {
        parent::__construct($maxBucketSize, $leakRateValue, $leakRateDuration);

        $this->redis = $redis ?: new \Predis\Client;
    }

    /**
     * { @inheritdoc }
     */
    public function attempt(string $resource, int $worth = 1): array
    {
        $script = '' .
            'local resourceLastUpdatedKey = KEYS[1] ' .
            'local resourceBucketSizeKey = KEYS[2] ' .
            'local maxBucketSize = tonumber(ARGV[1]) ' .
            'local leakRateValue = tonumber(ARGV[2]) ' .
            'local leakRateDuration = tonumber(ARGV[3]) ' .
            'local now = tonumber(ARGV[4]) ' .
            'local worth = tonumber(ARGV[5]) ' .

            // Milliseconds after which the bucket would be leaked completely
            'local leakFullTime = math.ceil((maxBucketSize * leakRateDuration)/leakRateValue) ' .

            // We keep the TTL of the REDIS keys to be of same seconds
            'local ttl = math.ceil(leakFullTime/1000) ' .
            'local resourceBucketSize = tonumber(redis.call(\'GET\', resourceBucketSizeKey)) ' .
            'if resourceBucketSize == nil then resourceBucketSize = 0 end ' .
            'local resourceLastUpdated = tonumber(redis.call(\'GET\', resourceLastUpdatedKey)) ' .
            'if resourceLastUpdated == nil then resourceLastUpdated = 0 end ' .

            // First leaks the bucket
            'resourceBucketSize = resourceBucketSize - math.max(0, (resourceLastUpdated - now)/(leakRateValue * leakRateDuration)) ' .
            'resourceLastUpdated = now ' .

            // Then if new request can be allowed attempt, fills the bucket with worth value
            'local allowAttempt = resourceBucketSize + worth <= maxBucketSize ' .
            'if allowAttempt then resourceBucketSize = resourceBucketSize + worth end ' .

            // Finally update the REDIS keys and return stats
            'redis.call(\'SETEX\', resourceLastUpdatedKey, ttl, resourceLastUpdated) ' .
            'redis.call(\'SETEX\', resourceBucketSizeKey, ttl, resourceBucketSize) ' .
            'return { allowAttempt, maxBucketSize, maxBucketSize - resourceBucketSize, now + leakFullTime }';

        $args = [
            $script,
            2,
            $resource . self::KEY1_SUFFIX,
            $resource . self::KEY2_SUFFIX,
            $this->maxBucketSize,
            $this->leakRateValue,
            $this->leakRateDuration,
            round(microtime(true) * 1000),
            $worth,
        ];

        return $this->redis->eval($script, ...$args);
    }
}
