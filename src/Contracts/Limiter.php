<?php

namespace Jitendra\PhpValve\Contracts;

use Jitendra\PhpValve\Base\Response;

interface Limiter
{
    /**
     * Attempts access to a throttled resource.
     *
     * @param  string      $resource
     * @param  int|integer $cost
     * @return Response
     */
    public function attempt(string $resource, int $cost): Response;
}
