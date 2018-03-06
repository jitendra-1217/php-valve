<?php

namespace Jitendra\PhpValve\Base;

class Response
{
    /**
     * @var bool
     */
    public $allowed;

    /**
     * @var int
     */
    public $limit;

    /**
     * @var int
     */
    public $remaining;

    /**
     * @var int
     */
    public $resetAt;

    /**
     * @var int
     */
    public $retryAfter;

    public function __construct(
        bool $allowed,
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
