<?php

namespace Jitendra\PhpValveTests\FixedBasic;

use Jitendra\PhpValve\FixedBasic;

final class RedisTest extends TestCase
{
    public function testFixedBasic()
    {
        $limiter = new FixedBasic\Redis(1000, 10);

        $this->makeAssertions($limiter);
    }

    public function testFixedBasicWithNonDefaultWorth()
    {
        $limiter       = new FixedBasic\Redis(1000, 10);
        $limiterLimit  = $limiter->limit();
        $limiterWindow = $limiter->window();
        $resource      = (string) rand(0, 10000);

        $this->makeAssertionsPerAttempt(
            $limiter,
            $resource,
            2,
            1,
            $limiterLimit,
            $limiterLimit - 2,
            millitime() + $limiterWindow,
            -1);
    }
}
