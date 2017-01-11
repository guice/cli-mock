<?php
/**
 * Warehouse.php
 *
 * User: Philip G
 * Date: 10/20/15
 */

namespace GP\Shipwise\Model;


use GP\Shipwise\Service\Sqlite;

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

    public function addProduct(Product $product, $quantity = 1)
    {
        /** @var Sqlite $s */
        $s = $this->c['Sqlite'];

        $stmt = $s->prepare(sprintf('SELECT * FROM ' . self::TABLE_WH_PRODUCTS . " WHERE product_id = '%s' AND warehouse_id = '%s'",
            $product->id, $this->id)); // Don't to filter these: not user input

        // Items exist, add them to the existing inventory
        if ( $row = $stmt->execute()->fetchArray(SQLITE3_ASSOC)) {
            $quantity += (int) $row['stock'];
        }

        $sql = sprintf('REPLACE INTO ' . self::TABLE_WH_PRODUCTS . " ( product_id, warehouse_id, stock )
                    VALUES ('%s', '%s', %d)", $product->id, $this->id, $quantity );

        $s->query($sql);
    }

    public static function getWarehouseList($c) {
        /** @var Sqlite $sqlite */
        $sqlite = $c['Sqlite'];
        $query = 'SELECT * FROM ' . self::TABLE_NAME;

        $res = $sqlite->query($query);

        $rows =[];
        while ( $r = $res->fetchArray(SQLITE3_ASSOC) ) {
            $rows[$r['name']] = (new self($c))->fromArray($r); // Name is unique. I can do this
        }

        return $rows;
    }
}