<?php
/**
 * Warehouse.php
 *
 * User: Philip G
 * Date: 10/20/15
 */

namespace GP\Shipwise\Model;


class Warehouse extends BaseModel
{

    use ObjectTrait;

    const TABLE_NAME = 'warehouse';
    const TABLE_WH_PRODUCTS = 'warehouse_products';

    protected $id;
    protected $name;
    protected $address;
    protected $latitude = 0;
    protected $longitude = 0;

    /**
     * I'm keeping things simple here: column list will map to class properties 1 to 1
     *
     * @var array
     */
    protected $columns = [
        'id',
        'name',
        'address',
        'latitude',
        'longitude',
    ];
}