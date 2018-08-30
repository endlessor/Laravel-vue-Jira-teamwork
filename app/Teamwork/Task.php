<?php

namespace App\Teamwork;

use App\JIRA\Issue;

/**
 * Class Task
 * @package App\Teamwork
 */
class Task
{
    private $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->data['id'];
    }

    public function getContent()
    {
        return $this->data['content'];
    }

    /**
     * Is this task completed?
     * @return boolean
     */
    public function isCompleted()
    {
        return $this->data['completed'];
    }

    /**
     * @param $key
     * @return bool
     */
    public function startsWith($key)
    {
        return starts_with($this->getContent(), $key);
    }

    /**
     * @param $key
     * @return bool
     */
    public function doesKeyMatch($key)
    {
        return $this->startsWith($key);
    }

    /**
     * @param $data
     * @return bool
     */
    public function haveFieldsChanged($data)
    {
        foreach ($data as $k => $v) {
            $original = $this->data[$k];

            if (is_string($original)) {
                $original = trim($original);
            }

            if (is_string($v)) {
                $v = trim($v);
            }

            if ($original != $v) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return bool
     */
    public function isDeleted()
    {
        return $this->data['status'] === 'deleted';
    }
}