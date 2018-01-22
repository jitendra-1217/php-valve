<?php

namespace Jitendra\PhpValveTests\LeakyBucket;

use Jitendra\PhpValve\LeakyBucket;

abstract class TestCase extends \Jitendra\PhpValveTests\TestCase
{
    protected function makeAssertions(LeakyBucket\Base $limiter)
    {
        $resource                = (string) rand(0, 10000);
        $limiterMaxBucketSize    = $limiter->maxBucketSize();
        $limiterLeakRateValue    = $limiter->leakRateValue();
        $limiterLeakRateDuration = $limiter->leakRateDuration();
        $limiterLeakFullTime     = $limiter->leakFullTime();

        // First many attempts up to burst limit must be allowed
        foreach (range(1, $limiterMaxBucketSize) as $i)
        {
            $this->makeAssertionsPerAttempt(
                $limiter,
                $resource,
                1,
                1,
                $limiterMaxBucketSize,
                $limiterMaxBucketSize - $i,
                millitime() + $limiterLeakFullTime,
                -1);
        }

        // Subsequent attempts must fail withing current leak duration
        $this->makeAssertionsPerAttempt(
            $limiter,
            $resource,
            1,
            0,
            $limiterMaxBucketSize,
            0,
            millitime() + $limiterLeakFullTime,
            millitime() + $limiterLeakRateDuration);

        // Sleeping for while will leak and allow for new requests
        sleep($limiterLeakRateDuration/1000);
        $this->makeAssertionsPerAttempt(
            $limiter,
            $resource,
            1,
            1,
            $limiterMaxBucketSize,
            $limiterLeakRateValue - 1,
            millitime() + $limiterLeakFullTime,
            -1);
    }
}
