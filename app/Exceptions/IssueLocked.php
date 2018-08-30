<?php

namespace App\Exceptions;

/**
 * Class IssueLocked
 * @package App\Exceptions
 */
class IssueLocked extends \Exception
{
    /**
     * @return IssueLocked
     */
    public static function make()
    {
        return new self("This issue is locked.");
    }
}