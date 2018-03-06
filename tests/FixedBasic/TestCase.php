<?php

namespace Jitendra\PhpValveTests\FixedBasic;

use Jitendra\PhpValve\FixedBasic;
use Jitendra\PhpValve\Base\Response;

abstract class TestCase extends \Jitendra\PhpValveTests\TestCase
{
    protected function makeAssertions(FixedBasic\Base $limiter)
    {
        $resource      = (string) rand(0, 10000);
        $limiterLimit  = $limiter->getLimit();
        $limiterWindow = $limiter->getWindow();

        // All attempts up to $limiterLimit withing current window must pass
        foreach (range(1, $limiterLimit) as $i)
        {
            $expected = new Response(true, $limiterLimit, $limiterLimit - $i, time() + $limiterWindow, -1);
            $this->attemptAndAssert($limiter, $resource, 1, $expected);
        }

        // Subsequent attempt in current window must fail
        $expected = new Response(false, $limiterLimit, 0, time() + $limiterWindow, time() + $limiterWindow);
        $this->attemptAndAssert($limiter, $resource, 1, $expected);

        // Once new window kicks in, new attempt must pass
        sleep($limiterWindow);
        $expected = new Response(true, $limiterLimit, $limiterLimit - 1, time() + $limiterWindow, -1);
        $this->attemptAndAssert($limiter, $resource, 1, $expected);
    }
}
