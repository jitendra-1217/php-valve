<?php

namespace Jitendra\PhpValveTests\LeakyBucket;

use Jitendra\PhpValve\LeakyBucket;
use Jitendra\PhpValve\Base\Response;

abstract class TestCase extends \Jitendra\PhpValveTests\TestCase
{
    protected function makeAssertions(LeakyBucket\Base $limiter)
    {
        $resource                = (string) rand(0, 10000);
        $limiterMaxBucketSize    = $limiter->getMaxBucketSize();
        $limiterLeakRateValue    = $limiter->getLeakRateValue();
        $limiterLeakRateDuration = $limiter->getLeakRateDuration();
        $limiterLeakFullTime     = $limiter->getLeakFullTime();

        // First many attempts up to burst limit must be allowed
        foreach (range(1, $limiterMaxBucketSize) as $i)
        {
            $expected = new Response(true, $limiterMaxBucketSize, $limiterMaxBucketSize - $i,  time() + $limiterLeakFullTime, -1);
            $this->attemptAndAssert($limiter, $resource, 1, $expected);
        }

        // Subsequent attempts must fail within current leak duration
        $expected = new Response(false, $limiterMaxBucketSize,  0,  time() + $limiterLeakFullTime,  time() + $limiterLeakRateDuration);
        $this->attemptAndAssert($limiter, $resource, 1, $expected);

        // Sleeping for while will leak and allow for new requests
        sleep($limiterLeakRateDuration);
        $expected = new Response(true, $limiterMaxBucketSize, $limiterLeakRateValue - 1,  time() + $limiterLeakFullTime, -1);
        $this->attemptAndAssert($limiter, $resource, 1, $expected);
    }
}
