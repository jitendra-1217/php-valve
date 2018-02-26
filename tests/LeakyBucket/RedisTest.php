<?php

namespace Jitendra\PhpValveTests\LeakyBucket;

use Jitendra\PhpValve\LeakyBucket;

final class RedisTest extends TestCase
{
    public function tearDown()
    {
        (new \Predis\Client)->flushall();
    }

    public function testLeakyBucket()
    {
        $limiter = new LeakyBucket\Redis(100, 1, 1);

        $this->makeAssertions($limiter);
    }

    public function testLeakyBucketWithNonDefaultWorth()
    {
        $limiter              = new LeakyBucket\Redis(100, 1, 1);
        $limiterMaxBucketSize = $limiter->getMaxBucketSize();
        $limiterLeakFullTime  = $limiter->getLeakFullTime();
        $resource             = (string) rand(0, 10000);

        $this->makeAssertionsPerAttempt(
            $limiter,
            $resource,
            2,
            1,
            $limiterMaxBucketSize,
            $limiterMaxBucketSize - 2,
            time() + $limiterLeakFullTime,
            -1);
    }
}
