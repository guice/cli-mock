<?php
/**
 * Product.php
 *
 * User: Philip G
 * Date: 10/20/15
 */

namespace GP\Shipwise\Model;

class Product extends BaseModel
{

    use ObjectTrait;

    const TABLE_NAME = 'product';
    const TABLE_WH_PRODUCTS = 'warehouse_products';

    protected $id;
    protected $name;
    protected $dimensions;
    protected $weight;

    /**
     * I'm keeping things simple here: column list will map to class properties 1 to 1
     *
     * @var array
     */
    protected $columns = [
        'id',
        'name',
        'dimensions',
        'weight',
    ];

}