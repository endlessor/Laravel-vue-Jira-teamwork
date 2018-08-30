<?php
namespace App\JIRA\Utils;

use ArrayAccess;

class Iterator implements \Iterator
{

    private $position = 0;

    /**
     * @var ArrayAccess
     */
    private $collection;

    /**
     * CollectionIterator constructor.
     * @param ArrayAccess $collection
     */
    public function __construct(ArrayAccess $collection)
    {
        $this->position = 0;
        $this->collection = $collection;

    }

    /**
     *
     */
    function rewind()
    {
        $this->position = 0;
    }

    /**
     * @return mixed
     */
    function current()
    {
        return $this->collection[$this->position];
    }

    /**
     * @return int
     */
    function key()
    {
        return $this->position;
    }

    /**
     *
     */
    function next()
    {
        ++$this->position;
    }

    /**
     * @return bool
     */
    function valid()
    {
        return isset($this->collection[$this->position]);
    }
}