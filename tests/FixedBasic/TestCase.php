<?php

namespace Jitendra\PhpValveTests\FixedBasic;

use Jitendra\PhpValve\FixedBasic;

abstract class TestCase extends \Jitendra\PhpValveTests\TestCase
{
    protected function makeAssertions(FixedBasic\Base $limiter)
    {
        $resource      = (string) rand(0, 10000);
        $limiterLimit  = $limiter->limit();
        $limiterWindow = $limiter->window();

        // All attempts up to $limiterLimit withing current window must pass
        foreach (range(1, $limiterLimit) as $i)
        {
            $this->makeAssertionsPerAttempt($limiter, $resource, 1, 1, $limiterLimit, $limiterLimit - $i, millitime() + $limiterWindow);
        }
        // Subsequent attempt in current window must fail
        $this->makeAssertionsPerAttempt($limiter, $resource, 1, 0, $limiterLimit, 0, millitime() + $limiterWindow);
        // Once new window kicks in, new attempt must pass
        sleep($limiterWindow/1000);
        $this->makeAssertionsPerAttempt($limiter, $resource, 1, 1, $limiterLimit, $limiterLimit - 1, millitime() + $limiterWindow);
    }
}
