<?php
/**
 * SqliteTrait.php
 *
 * User: Philip G
 * Date: 10/20/15
 */

namespace GP\Shipwise\Model;


trait ObjectTrait
{
    public function getTableName() {
        return static::TABLE_NAME;
    }

    public function toArray() {
        $arr = [];
        foreach ( $this->columns as $name ) {
            $arr[$name] = $this->$name;
        }
        return $arr;
    }

    public function toSqlArray() {
        $arr = [];
        foreach ( $this->columns as $name ) {
            $arr[':' . $name] = $this->$name;
        }
        return $arr;
    }

    public function fromArray( $arr ) {
        foreach ($arr as $name => $v ) {
            $this->{$name} = $v;
        }

        return $this;
    }

    public function __set($prop, $value)
    {
        if (method_exists($this, 'set' . $prop)) {
            $this->{'set' . $prop}($value);
        } else {
            $this->$prop = $value;
        }
    }

    public function __get($prop)
    {
        return method_exists($this, 'get' . $prop) ? $this->{'get' . $prop}() : $this->$prop;
    }

}