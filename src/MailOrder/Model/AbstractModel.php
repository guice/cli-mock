<?php
/**
 * Created by PhpStorm.
 * User: philip
 * Date: 8/8/16
 * Time: 10:39 AM
 */

namespace App\MailOrder\Model;

use App\Lib\BaseObject;

abstract class AbstractModel
{
    use BaseObject; // Imports base __set, __get calls

    abstract protected function getFieldSequence();

    public function __construct(array $r)
    {
        $keys = $this->getFieldSequence();

        if (count($keys) != count($r)) {
            throw new Exception('CSV array count does not match object property count.');
        }

        foreach ($r as $idx => $value) {
            $this->{$keys[$idx]} = $value;
        }
    }

}