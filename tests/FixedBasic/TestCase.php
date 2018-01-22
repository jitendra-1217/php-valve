<?php

namespace Jitendra\PhpValveTests\FixedBasic;

use Jitendra\PhpValve\FixedBasic;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    protected function makeAssertions(FixedBasic\Base $limiter)
    {
        $now           = millitime();
        $resource      = (string) rand(0, 10000);
        $limiterLimit  = $limiter->limit();
        $limiterWindow = $limiter->window();

        // All attempts up to $limiterLimit withing current window must pass
        foreach (range(1, $limiterLimit) as $i)
        {
            $this->makeAssertionsPerAttempt($limiter, $resource, 1, 1, $limiterLimit, $limiterLimit - $i, $now + $limiterWindow);
        }
        // Subsequent attempt in current window must fail
        $this->makeAssertionsPerAttempt($limiter, $resource, 1, 0, $limiterLimit, 0, $now + $limiterWindow);
        // Once new window kicks in, new attempt must pass
        sleep($limiterWindow/1000);
        $this->makeAssertionsPerAttempt($limiter, $resource, 1, 1, $limiterLimit, $limiterLimit - 1, $now + 2 * $limiterWindow);
    }

    protected function makeAssertionsPerAttempt(FixedBasic\Base $limiter, string $resource, int $worth, ...$expected)
    {
        list($allowed, $limit, $remaining, $reset) = $limiter->attempt($resource, $worth);

        $this->assertSame($expected[0], $allowed);
        $this->assertSame($expected[1], $limit);
        $this->assertSame($expected[2], $remaining);
        $this->assertEquals($expected[3], $reset, '', 50);
    }
}
