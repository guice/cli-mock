<?php
/**
 * Product.php
 *
 * User: Philip G
 * Date: 10/20/15
 */

namespace GP\Shipwise\Model;

use GP\Shipwise\Service\Sqlite;

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

    public function assignTo(Warehouse $warehouse, $quantity = 1)
    {
        $warehouse->addProduct($this, $quantity);
    }

    public static function locateStock($orderId, $c) {

        /**
            SELECT warehouse_id as warehouse_id FROM order_products op
                JOIN warehouse_products as wp ON wp.product_id = op.product_id AND wp.stock >= op.quantity
            WHERE
                op.order_id = 'fc8f0bb8-7869-11e5-9eea-7831c1d5b924'
         */

        $sql = sprintf("SELECT wp.warehouse_id FROM order_products op
              JOIN warehouse_products as wp ON wp.product_id = op.product_id AND wp.stock >= op.quantity
          WHERE op.order_id = '%s'", $orderId);

        /** @var Sqlite $s */
        $s = $c['Sqlite'];
        $stmnt = $s->prepare($sql);

        $whs = [];
        $res = $stmnt->execute();
        while ( $r = $res->fetchArray(SQLITE3_ASSOC) ) {
            $whs[] = $r['warehouse_id'];
        }

        return $whs;
    }

    public static function getProductList($c)
    {
        /** @var Sqlite $sqlite */
        $sqlite = $c['Sqlite'];
        $query = 'SELECT * FROM ' . self::TABLE_NAME;

        $res = $sqlite->query($query);

        $rows = [];
        while ($r = $res->fetchArray(SQLITE3_ASSOC)) {
            $rows[$r['name']] = (new self($c))->fromArray($r); // Name is unique. I can do this
        }

        return $rows;
    }
}