<?php

namespace Jitendra\PhpValve\LeakyBucket;

use Jitendra\PhpValve\Contracts\Throttler;

abstract class Base implements Throttler
{
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

    public function __construct(
        int $maxBucketSize,
        int $leakRateValue,
        int $leakRateDuration)
    {
        $this->maxBucketSize    = $maxBucketSize;
        $this->leakRateValue    = $leakRateValue;
        $this->leakRateDuration = $leakRateDuration;
    }
}
