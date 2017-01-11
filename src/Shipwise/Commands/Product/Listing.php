<?php
/**
 * Warehouse.php
 *
 * User: Philip G
 * Date: 10/20/15
 */

namespace GP\Shipwise\Command\Product;


use Pimple\Container;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use GP\Shipwise\Model\Product;

class Listing extends Command
{
    /**
     * @var Container
     */
    protected $c;

    public function __construct($c)
    {
        $this->c = $c;
        parent::__construct('Product Listing');
    }

    protected function configure()
    {
        $this
            ->setName('product:list')
            ->setDescription('List of existing Products');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $list = Product::getProductList($this->c);

        $rows = [];
        foreach ($list as $pr) {
            $rows[] = [
                'uuid' => $pr->id,
                'name' => $pr->name,
                'dimensions' => $pr->dimensions,
                'weight' => $pr->weight
            ];
        }

        $table = new Table($output);
        $table->setHeaders(['UUID', 'Name', 'Dimensions', 'Weight'])
            ->setRows($rows);

        $table->render();
    }
}