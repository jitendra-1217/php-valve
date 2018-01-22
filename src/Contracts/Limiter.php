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
     *                               - Allowed or not?
     *                               - X-RateLimit-Limit
     *                               - X-RateLimit-Remaining
     *                               - X-RateLimit-Reset (in milliseconds)
     */
    public function attempt(string $resource, int $worth = 1): array;
}
