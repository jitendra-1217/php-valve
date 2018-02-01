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
     * Bucket leak rate duration in milliseconds.
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

    public function maxBucketSize(): int
    {
        return $this->maxBucketSize;
    }

    public function leakRateValue(): int
    {
        return $this->leakRateValue;
    }

    public function leakRateDuration(): int
    {
        return $this->leakRateDuration;
    }

    public function leakFullTime(): int
    {
        return ceil($this->maxBucketSize * $this->leakRateDuration / $this->leakRateValue);
    }
}
