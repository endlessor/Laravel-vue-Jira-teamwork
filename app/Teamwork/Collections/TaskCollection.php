<?php

namespace App\Teamwork\Collections;

use App\Teamwork\Task;
use Illuminate\Support\Collection;

/**
 * Class TaskCollection
 * @package App\Teamwork\Collections
 */
class TaskCollection extends Collection
{
    /**
     * @param $key
     * @return mixed|null
     */
    public function getFromKey($key)
    {
        /** @var Task $v */
        foreach ($this as $v) {
            if ($v->doesKeyMatch($key)) {
                return $v;
            }
        }

        return null;
    }
}