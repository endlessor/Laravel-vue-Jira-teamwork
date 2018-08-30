<?php

namespace App\JIRA;
use App\Exceptions\IssueLocked;
use Redis;

/**
 * Class Issue
 * @package App\JIRA
 */
class Issue
{
    private $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->data['key'];
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->data['id'];
    }

    /**
     * @param $name
     * @return mixed
     */
    public function getField($name)
    {
        if (isset($this->data['fields'][$name])) {
            return $this->data['fields'][$name];
        }
        return null;
    }

    /**
     * @return string
     */
    public function getSummary()
    {
        return $this->getField('summary');
    }

    /**
     * Try to lock the issue for processing.
     * @throws IssueLocked
     */
    public function lock()
    {
        $lockName = $this->getLockName();

        $lock = Redis::exists($lockName);
        if (!$lock) {
            Redis::setex($lockName, 60 * 5, 1);
            return;
        }

        throw IssueLocked::make();
    }

    /**
     *
     */
    public function unlock()
    {
        $lockName = $this->getLockName();
        Redis::del($lockName);
    }

    /**
     * @return string
     */
    private function getLockName()
    {
        return 'issue:' . $this->getId() . ':lock';
    }
}