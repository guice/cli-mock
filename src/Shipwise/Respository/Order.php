<?php
/**
 * Order.php
 *
 * User: Philip G
 * Date: 10/20/15
 */

namespace GP\Shipwise\Model;


use GP\Shipwise\Service\Sqlite;

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

    public function add ( array $products, $quantity = 1 ) {
        /** @var Sqlite $s */
        $s = $this->c['Sqlite'];

        foreach ($products as $p ) {
            $sql = sprintf('REPLACE INTO ' . self::TABLE_OR_PRODUCTS . " ( order_id, product_id, quantity )
                    VALUES ('%s', '%s', %d)", $this->id, $p->id, $quantity);
            $s->query($sql);
        }
    }

    public function assignWarehouse ( array $warehouses ) {
        /**
            SELECT *, ((32.920651 - latitude)*(32.920651 - latitude)) + ((-96.7370569 - longitude)*(-96.7370569 - longitude)) as distance FROM warehouse
            WHERE warehouse_id IN ('')
            ORDER BY distance ASC
            limit 1;
         */

        $sql =  sprintf("SELECT *, ((%f - latitude)*(%f - latitude)) + ((%f - longitude)*(%f - longitude)) as distance
            FROM warehouse
            WHERE id IN ('%s')
            ORDER BY distance ASC
            limit 1;",  $this->latitude, $this->latitude, $this->longitude, $this->longitude, join("', '", $warehouses));

        $s = $this->c['Sqlite'];
        $stmnt = $s->prepare($sql);
        $r = $stmnt->execute()->fetchArray(SQLITE3_ASSOC) ;

        $warehouse = (new Warehouse($this->c))->fromArray($r);

        $s->exec('UPDATE `' . self::TABLE_NAME . "` SET warehouse_id = '{$warehouse->id}' WHERE id = '{$this->id}'");

        return [$warehouse, $r['distance']];
    }
}