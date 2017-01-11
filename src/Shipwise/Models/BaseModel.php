<?php
/**
 * BaseModel.php
 *
 * User: Philip G
 * Date: 10/21/15
 */

namespace GP\Shipwise\Model;

use Pimple\Container;
use Ramsey\Uuid\Uuid;

abstract class BaseModel implements SqliteInterface
{

    /**
     * @var Container
     */
    protected $c;

    public function __construct($c) {
        $this->c = $c;
    }

    public static function get($id, $c) {
        $self = new static($c);
        $self->id = $id;

        return $self;
    }

    public static function create ($args, $c) {
        $self = new static($c);

        $args['id'] = (string) Uuid::uuid1();
        $self->fromArray($args);

        return $self;
    }
}