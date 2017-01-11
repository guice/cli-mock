<?php
/**
 * Order.php
 *
 * User: Philip G
 * Date: 10/20/15
 */

namespace GP\Shipwise\Model;


class Order extends BaseModel
{
    use ObjectTrait;

    const TABLE_NAME = 'order';
    const TABLE_OR_PRODUCTS = 'order_products';

    protected $id;
    protected $ship_addr;
    protected $longitude;
    protected $latitude;

    /**
     * I'm keeping things simple here: column list will map to class properties 1 to 1
     *
     * @var array
     */
    protected $columns = [
        'id',
        'ship_addr',
        'latitude',
        'longitude',
    ];

}