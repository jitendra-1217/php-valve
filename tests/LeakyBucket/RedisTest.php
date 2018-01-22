<?php

namespace Jitendra\PhpValveTests\LeakyBucket;

final class RedisTest extends TestCase
{
    public function testLeakyBucket()
    {
        $this->makeAssertions(100, 1, 1000); // Leak at 1 per second with burst 100
    }

    public function testLeakyBucketWithNonDefaultWorth()
    {
        // Instantiate limiter with predefined arguments
        $resource = (string) rand(0, 10000);
        list($maxBucketSize, $leakRateValue, $leakRateDuration) = [100, 1, 1000];
        $limiter = new \Jitendra\PhpValve\LeakyBucket\Redis($maxBucketSize, $leakRateValue, $leakRateDuration);

        list($allowed, $limit, $remaining, $reset) = $limiter->attempt($resource, 10);
        $this->assertSame(1, $allowed);
        $this->assertSame($maxBucketSize, $limit);
        $this->assertSame($maxBucketSize - 10, $remaining);
        $this->assertEquals(millitime() + 100000, $reset, '', 20);
    }
}
