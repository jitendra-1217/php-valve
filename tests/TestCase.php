<?php

namespace Jitendra\PhpValveTests;

use Jitendra\PhpValve\Contracts;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    protected function makeAssertionsPerAttempt(Contracts\Limiter $limiter, string $resource, int $worth, ...$expected)
    {
        list($allowed, $limit, $remaining, $reset, $retryAfter) = $limiter->attempt($resource, $worth);

        $this->assertSame($expected[0], $allowed);
        $this->assertSame($expected[1], $limit);
        $this->assertSame($expected[2], $remaining);
        $this->assertEquals($expected[3], $reset, '', 50);
        $this->assertEquals($expected[4], $retryAfter, '', 50);
    }
}
