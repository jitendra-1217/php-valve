<?php

namespace Jitendra\PhpValveTests\FixedBasic;

use Jitendra\PhpValve\FixedBasic;
use Jitendra\PhpValve\Base\Response;

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

    public function testFixedBasicWithNonDefaultCost()
    {
        $limiter       = new FixedBasic\Redis(1, 10);
        $limiterLimit  = $limiter->getLimit();
        $limiterWindow = $limiter->getWindow();
        $resource      = (string) rand(0, 10000);

        $expected = new Response(true, $limiterLimit, $limiterLimit - 2, time() + $limiterWindow, -1);
        $this->attemptAndAssert($limiter, $resource, 2, $expected);
    }
}
