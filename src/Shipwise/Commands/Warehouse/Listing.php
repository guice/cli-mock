<?php
/**
 * Warehouse.php
 *
 * User: Philip G
 * Date: 10/20/15
 */

namespace GP\Shipwise\Command\Warehouse;


use Pimple\Container;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use GP\Shipwise\Model\Warehouse as WarehouseModel;

class Listing extends Command
{
    /**
     * @var Container
     */
    protected $c;

    public function __construct($c)
    {
        $this->c = $c;
        parent::__construct('Warehouse Listing');
    }

    protected function configure()
    {
        $this
            ->setName('warehouse:list')
            ->setDescription('List existing Warehouses');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $list = WarehouseModel::getWarehouseList($this->c);

        $rows = [];
        foreach ($list as $wh) {
            $rows[] = [
                'uuid' => $wh->id,
                'name' => $wh->name,
                'address' => $wh->address
            ];
        }

        $table = new Table($output);
        $table->setHeaders(['UUID', 'Name', 'Address'])
            ->setRows($rows);

        $table->render();
    }
}