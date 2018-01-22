<?php

namespace Jitendra\PhpValveTests\LeakyBucket;

use Jitendra\PhpValve\LeakyBucket;

final class RedisTest extends TestCase
{
    public function testLeakyBucket()
    {
        $limiter = new LeakyBucket\Redis(100, 1, 1000);

        $this->makeAssertions($limiter);
    }

    public function testLeakyBucketWithNonDefaultWorth()
    {
        $limiter              = new LeakyBucket\Redis(100, 1, 1000);
        $limiterMaxBucketSize = $limiter->maxBucketSize();
        $limiterLeakFullTime  = $limiter->leakFullTime();
        $resource             = (string) rand(0, 10000);

        $this->makeAssertionsPerAttempt(
            $limiter,
            $resource,
            2,
            1,
            $limiterMaxBucketSize,
            $limiterMaxBucketSize - 2,
            millitime() + $limiterLeakFullTime,
            -1);
    }
}
