<?php

namespace Jitendra\PhpValve\FixedBasic;

use Jitendra\PhpValve\Contracts\Limiter;
use Jitendra\PhpValve\Traits\HasAttributes;

abstract class Base implements Limiter
{
    use HasAttributes;

    /**
     * Window size in milliseconds.
     *
     * @var int
     */
    protected $window;

    /**
     * Max attempts allowed in above window.
     *
     * @var int
     */
    protected $limit;

    public function __construct(int $window, int $limit)
    {
        $this->window    = $window;
        $this->limit     = $limit;
    }

    public function window(): int
    {
        return $this->window;
    }

    public function limit(): int
    {
        return $this->limit;
    }
}
