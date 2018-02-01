<?php

namespace Jitendra\PhpValve\Traits;

/**
 * Command attributes, getters & setters for throttlers classes
 */
trait HasAttributes
{
    /**
     * Redis key prefix. Useful if multiple limiters are being
     * used on same identifier.
     *
     * @var string
     */
    protected $keyPrefix = 'pv:';

    public function keyPrefix(): string
    {
        return $this->keyPrefix;
    }

    public function setKeyPrefix(string $prefix)
    {
        $this->keyPrefix = $prefix;
    }
}