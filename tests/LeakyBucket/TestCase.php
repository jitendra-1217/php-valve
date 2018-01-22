<?php

namespace Jitendra\PhpValveTests\LeakyBucket;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    protected function makeAssertions($maxBucketSize, $leakRateValue, $leakRateDuration)
    {
        // A random resource identifier
        $resource = (string) rand(0, 10000);

        $limiter = new \Jitendra\PhpValve\LeakyBucket\Redis($maxBucketSize, $leakRateValue, $leakRateDuration);

        // Asserts the initial burst, i.e. first 100 attempts withing $leakRateDuration must be allowed
        foreach (range(1, $maxBucketSize) as $i)
        {
            list($allowed, $limit, $remaining, $reset) = $limiter->attempt($resource);

            $this->assertSame(1, $allowed);
            $this->assertSame($maxBucketSize, $limit);
            $this->assertSame($maxBucketSize - $i, $remaining);
            $this->assertEquals(millitime() + 100000, $reset, '', 20);
        }

        // Subsequent attempts must fail
        foreach (range(1, 2) as $i)
        {
            list($allowed, $limit, $remaining, $reset) = $limiter->attempt($resource);
            $this->assertSame(0, $allowed);
            $this->assertSame(0, $remaining);
            // Not necessary to assert other items again
        }

        // After $leakRateDuration, again steady rate of requests be allowed
        foreach (range(1, 5) as $i)
        {
            // Following should have leaked $leakRateValue units and so that many requests could be made now without fail
            sleep($leakRateDuration/1000);
            foreach (range(1, $leakRateValue) as $j)
            {
                list($allowed, $limit, $remaining, $reset) = $limiter->attempt($resource);
                $this->assertSame(1, $allowed);
                $this->assertSame($leakRateValue - $j, $remaining);
            }
            // Following attempt must fail again
            list($allowed, $limit, $remaining, $reset) = $limiter->attempt($resource);
            $this->assertSame(0, $allowed);
            $this->assertSame(0, $remaining);
        }
    }
}
