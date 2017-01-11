<?php
/**
 * Created by PhpStorm.
 * User: philip
 * Date: 8/9/16
 * Time: 7:39 AM
 */

namespace App\Lib;


trait BaseObject
{
    public function __set($property, $value)
    {
        $setter = 'set' . $property;

        if (method_exists($this, $setter)) {
            $this->$setter($value);
        } else {
            $this->$property = $value;
        }
    }

    public function __get($property)
    {
        $getter = 'get' . $property;
        return method_exists($this, $getter) ? $this->$getter : $this->$property;
    }

}