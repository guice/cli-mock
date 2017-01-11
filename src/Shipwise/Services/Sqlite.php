<?php
/**
 * Sqlite.php
 *
 * User: Philip G
 * Date: 10/20/15
 */

namespace GP\Shipwise\Service;


use GP\Shipwise\Model\SqliteInterface;

class Sqlite extends \SQLite3
{

    protected static $db;
    protected $c;

    // TODO Currently not needed just yet. Maybe soon.
    public function __construct($c)
    {
        $this->c = $c;
        parent::__construct($c['config']['sqlite']);
    }

    public function load(SqliteInterface $model) {

    }

    public function save(SqliteInterface $model)
    {
        $values = $model->toArray();
        $table = $model->getTableName();

        $sql = 'INSERT INTO `' . $table . '` (`' . join('`, `', array_keys($values)) . '`) '
            . " VALUES ( '" . join("', '",

                array_map(function ($v) {
                    return $this->escapeString($v);
                }, array_values($values))) . "')";

        $this->exec($sql);
        if ($this->lastErrorCode()) {
            throw new \Exception($this->lastErrorMsg());
        }

        return $values['id'];
    }
}