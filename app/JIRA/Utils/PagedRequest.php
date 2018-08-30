<?php

namespace App\JIRA\Utils;

use App\JIRA\Tenant;
use App\JWTRequest;

use ArrayAccess;
use Countable;
use IteratorAggregate;
use Traversable;

/**
 * Class PagedRequest
 * @package App\JIRA\Utils
 */
class PagedRequest implements ArrayAccess, IteratorAggregate, Countable
{
    const LIMIT = 50;

    /**
     * @var Tenant
     */
    private $tenant;

    /**
     * @var string
     */
    private $itemsKey = 'items';

    /**
     * @var \Closure
     */
    private $transformer;

    /**
     * @var mixed
     */
    private $items = [];

    /**
     * @var int
     */
    private $totalItems = 0;

    /**
     * @var string
     */
    private $path;

    /**
     * @var string
     */
    private $parameters;

    /**
     * @var bool
     */
    private $initialized = false;

    /**
     * @var int
     */
    private $loaded = 0;

    /**
     * PagedRequest constructor.
     * @param Tenant $tenant
     * @param $path
     * @param array $parameters
     */
    public function __construct(Tenant $tenant, $path, array $parameters)
    {
        $this->tenant = $tenant;
        $this->path = $path;
        $this->parameters = $parameters;
    }

    public function init()
    {
        $this->initialized = true;

        $this->loaded = 0;
        $this->items = [];

        $this->loadNextPage();
    }

    private function loadNextPage()
    {
        $parameters = $this->parameters;

        $parameters['limit'] = self::LIMIT;
        $parameters['startAt'] = $this->loaded;

        // Execute initial request
        if (strpos($this->path, '?') >= 0) {
            $fullPath = $this->path . '&' . http_build_query($parameters);
        } else {
            $fullPath = $this->path . '?' . http_build_query($parameters);
        }

        $this->log('Loading: ' . $fullPath);

        $request = new JWTRequest($this->tenant);
        $json = $request->get($fullPath);

        $this->totalItems = $json['total'];

        foreach ($json[$this->itemsKey] as $v) {
            $this->items[$this->loaded] = $this->convert($v);
            $this->loaded ++;
        }
    }

    /**
     *
     */
    private function checkInitialized()
    {
        if (!$this->initialized) {
            $this->init();
        }
    }

    /**
     * @return string
     */
    public function getItemsKey()
    {
        return $this->itemsKey;
    }

    /**
     * @param string $itemsKey
     * @return PagedRequest
     */
    public function setItemsKey($itemsKey)
    {
        $this->itemsKey = $itemsKey;
        return $this;
    }

    /**
     * @return \Closure
     */
    public function getTransformer()
    {
        return $this->transformer;
    }

    /**
     * @param \Closure $transformer
     * @return PagedRequest
     */
    public function setTransformer($transformer)
    {
        $this->transformer = $transformer;
        return $this;
    }

    /**
     * @param $v
     * @return mixed
     */
    private function convert($v)
    {
        if (isset($this->transformer)) {
            return call_user_func($this->transformer, $v);
        } else {
            return $v;
        }
    }

    private function log($msg)
    {
        \Log::info($msg);
    }

    /**
     * Count elements of an object
     * @link http://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     * </p>
     * <p>
     * The return value is cast to an integer.
     * @since 5.1.0
     */
    public function count()
    {
        $this->checkInitialized();
        return $this->totalItems;
    }

    /**
     * Whether a offset exists
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset <p>
     * An offset to check for.
     * </p>
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     * @since 5.0.0
     */
    public function offsetExists($offset)
    {
        $this->checkInitialized();
        return $this->totalItems > $offset;
    }

    /**
     * Offset to retrieve
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     * @return mixed Can return all value types.
     * @since 5.0.0
     */
    public function offsetGet($offset)
    {
        $this->checkInitialized();

        while ($this->loaded <= $offset) {
            $this->loadNextPage();
        }

        return $this->items[$offset];
    }

    /**
     * Offset to set
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset <p>
     * The offset to assign the value to.
     * </p>
     * @param mixed $value <p>
     * The value to set.
     * </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetSet($offset, $value)
    {
        $this->items[$offset] = $value;
    }

    /**
     * Offset to unset
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetUnset($offset)
    {
        unset($this->items[$offset]);
    }

    /**
     * Retrieve an external iterator
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     * <b>Traversable</b>
     * @since 5.0.0
     */
    public function getIterator()
    {
        return new Iterator($this);
    }
}