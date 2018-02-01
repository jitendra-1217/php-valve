<?php

if (! function_exists('millitime'))
{
    /**
     * Returns current milliseconds value
     *
     * @return int
     */
    function millitime(): int
    {
        return (int) round(microtime(true) * 1000);
    }
}
