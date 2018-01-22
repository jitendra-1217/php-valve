<?php

if (! function_exists('millitime'))
{
    function millitime()
    {
        return (int) round(microtime(true) * 1000);
    }
}
