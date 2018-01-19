<?php

namespace Jitendra\PhpValve\Contracts;

interface Throttler
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
     *                               - ResetAt
     */
    public function attempt(string $resource, int $worth = 1): array;
}
