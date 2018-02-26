<?php

namespace Jitendra\PhpValve\Base;

use Jitendra\PhpValve\Contracts;

class Response implements Contracts\Response
{
    public $allowed;
    public $limit;
    public $remaining;
    public $resetAt;
    public $retryAfter;

    public function __construct(
        int $allowed,
        int $limit,
        int $remaining,
        int $resetAt,
        int $retryAfter)
    {
        $this->allowed    = $allowed;
        $this->limit      = $limit;
        $this->remaining  = $remaining;
        $this->resetAt    = $resetAt;
        $this->retryAfter = $retryAfter;
    }
}
