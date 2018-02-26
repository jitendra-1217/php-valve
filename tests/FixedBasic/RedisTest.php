<?php

namespace Jitendra\PhpValveTests\FixedBasic;

use Jitendra\PhpValve\FixedBasic;

final class RedisTest extends TestCase
{
    public function tearDown()
    {
        (new \Predis\Client)->flushall();
    }

    public function testFixedBasic()
    {
        $limiter = new FixedBasic\Redis(1, 10);

        $this->makeAssertions($limiter);
    }

    public function testFixedBasicWithNonDefaultWorth()
    {
        $limiter       = new FixedBasic\Redis(1, 10);
        $limiterLimit  = $limiter->getLimit();
        $limiterWindow = $limiter->getWindow();
        $resource      = (string) rand(0, 10000);

        $this->makeAssertionsPerAttempt(
            $limiter,
            $resource,
            2,
            1,
            $limiterLimit,
            $limiterLimit - 2,
            time() + $limiterWindow,
            -1);
    }
}
