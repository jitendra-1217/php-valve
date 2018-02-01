<?php

namespace Jitendra\PhpValve\Contracts;

interface Limiter
{
    /**
     * Attempts access to a throttled resource.
     *
     * Returns following in order:
     * - Allowed or not?
     * - X-RateLimit-Limit
     * - X-RateLimit-Remaining
     * - X-RateLimit-Reset (in milliseconds)
     * - X-RateLimit-RetryAfter
     *
     * @param  string      $resource
     * @param  int|integer $worth
     * @return array
     */
    public function attempt(string $resource, int $worth = 1): array;
}
