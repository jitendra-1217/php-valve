<?php

namespace Jitendra\PhpValveTests;

use Jitendra\PhpValve\Contracts;
use Jitendra\PhpValve\Base\Response;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    protected function attemptAndAssert(
        Contracts\Limiter $limiter,
        string $resource,
        int $cost,
        Response $expected)
    {
        $actual = $limiter->attempt($resource, $cost);
        $this->assertEquals($expected, $actual);
    }
}
