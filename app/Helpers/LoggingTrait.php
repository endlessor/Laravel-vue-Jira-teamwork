<?php

namespace App\Helpers;

/**
 * Trait LoggingTrait
 * @package App\Helpers
 */
trait LoggingTrait
{
    /**
     * Return the string that will be placed in front of all logs.
     * @return string
     */
    protected abstract function getLogPrefix();

    /**
     * @param string $message
     * @return bool
     */
    protected function info($message)
    {
        return \Log::info($this->getLogPrefix() . ' ' . $message);
    }

    /**
     * @param string $message
     * @return bool
     */
    protected function warning($message)
    {
        return \Log::warning($this->getLogPrefix() . ' ' . $message);
    }

    /**
     * @param string $message
     * @return bool
     */
    protected function error($message)
    {
        return \Log::error($this->getLogPrefix() . ' ' . $message);
    }
}