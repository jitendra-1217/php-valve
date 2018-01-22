<?php

namespace Jitendra\PhpValve\Contracts;

interface Limiter
{
    /**
     * Attempts access to a throttled resource.
     *
     * @param  string      $resource
     * @param  int|integer $worth
     * @return array                 [bool, int, int, int]
     *                               - If attempt is to be allowed or not
     *                               - Limit
     *                               - Remaining
     *                               - Approximate Reset
     */
    public function attempt(string $resource, int $worth = 1): array;
}