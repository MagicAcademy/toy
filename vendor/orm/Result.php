<?php declare(strict_types=1);

namespace orm;

use \Iterator;

class Result implements Iterator
{
    protected $relations = [];

    protected $rows = null;

    protected $current = null;

    protected $isObject = false;

    protected $tmpObject = [];

    protected $key = 0;

    protected $count = 0;

    public function __construct($rows)
    {
        $this->isObject = is_object($rows) || is_null($rows);

        if ($this->isObject) {
            $this->rows = $rows;
            $this->tmpObject[] = $rows;
            $this->count = count($this->tmpObject);
        } else {
            $this->rows = $rows;
            $this->count = count($rows);
        }
    }

    public function setRelation(array $relations)
    {
        $this->relations = $relations;
    }

    public function current()
    {
        return $this;
    }

    public function key()
    {
        return $this->key;
    }

    public function next()
    {
        $this->current = $this->isObject?$this->tmpObject[$this->key++]:$this->rows[$this->key++];
        return $this->current;
    }

    public function rewind()
    {
        $this->key = 0;
        $this->current = $this->isObject?$this->tmpObject[$this->key]:$this->rows[$this->key];
    }

    public function valid(): bool
    {
        return $this->key < $this->count;
    }

    public function __get($name)
    {
        if ($this->isObject && isset($this->rows->$name)) {
            return $this->rows->$name;
        } else if (!$this->isObject && isset($this->rows[$this->key]->$name)) {
            return $this->rows[$this->key]->$name;
        }
    }
}