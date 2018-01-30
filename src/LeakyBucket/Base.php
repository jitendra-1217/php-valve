<?php

namespace Jitendra\PhpValve\LeakyBucket;

use Jitendra\PhpValve\Contracts\Limiter;
use Jitendra\PhpValve\Traits\HasAttributes;

abstract class Base implements Limiter
{
    use HasAttributes;

    /**
     * Max bucket size.
     *
     * @var int
     */
    protected $maxBucketSize;

    /**
     * Bucket leak rate value.
     *
     * @var int
     */
    protected $leakRateValue;

    /**
     * Bucket leak rate duration in seconds.
     *
     * @var int
     */
    protected $leakRateDuration;

    public function __construct(int $maxBucketSize, int $leakRateValue, int $leakRateDuration)
    {
        $this->maxBucketSize    = $maxBucketSize;
        $this->leakRateValue    = $leakRateValue;
        $this->leakRateDuration = $leakRateDuration;
    }

    public function getMaxBucketSize(): int
    {
        return $this->maxBucketSize;
    }

    public function getLeakRateValue(): int
    {
        return $this->leakRateValue;
    }

    public function getLeakRateDuration(): int
    {
        return $this->leakRateDuration;
    }

    /**
     * Returns total time it would take to completely leak the bucket, used
     * in calculating retry-after (now + this value).
     *
     * @return int
     */
    public function getLeakFullTime(): int
    {
        return ceil(($this->maxBucketSize * $this->leakRateDuration) / $this->leakRateValue);
    }
}
