<?php

namespace Jitendra\PhpValve\Traits;

/**
 * Common attributes, getters & setters for limiters classes
 */
trait HasAttributes
{
    /**
     * Resource key prefix.
     * Useful if multiple limiters are being used on same identifier.
     *
     * @var string
     */
    protected $prefix = 'pv:';

    public function getPrefix(): string
    {
        return $this->prefix;
    }

    public function setPrefix(string $prefix)
    {
        $this->prefix = $prefix;
    }
}
