<?php
/**
 * Created by PhpStorm.
 * User: philip
 * Date: 12/8/16
 * Time: 5:27 PM
 */

namespace App\Repository;


use Pimple\Container;

class AbstractRepository
{

    /**
     * @var Container
     */
    protected $c;

    public function __construct( Container $c ) {
        $this->c = $c;
    }

}