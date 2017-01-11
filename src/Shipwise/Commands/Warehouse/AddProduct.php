<?php
/**
 * AddProduct.php
 *
 * User: Philip G
 * Date: 10/21/15
 */

namespace GP\Shipwise\Command\Warehouse;

use Pimple\Container;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use GP\Shipwise\Model\Warehouse;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;

class AddProduct extends Command
{
    /**
     * @var Container
     */
    protected $c;

    public function __construct($c)
    {
        $this->c = $c;
        parent::__construct('Add Product to Warehouse');
    }

    protected function configure()
    {
        $this
            ->setName('warehouse:addproduct')
            ->setDescription('Add Product to wearhouse')
            ->addArgument('id', InputArgument::OPTIONAL, 'Warehouse UUID');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $sqlfile = $this->c['config']['sqlite'];
        $helper = $this->getHelper('question');

        if ( !$uuid = $input->getArgument('uuid') ) {
            $warehouses = Warehouse::getWarehouseList($this->c);

            $t = $helper->ask($input, $output, new ChoiceQuestion(
                'Choose a warehouse to add products',
                array_keys($warehouses)
            ));

            $wh = $warehouses[$t];
        } else {
            $sqlite = $this->c['Sqlite'];
            $wh = $sqlite->load(Warehouse::get($uuid, $this->c));
        }



        $quantity = $helper->ask($input, $output, new Question('Product Quantity: '));


        $output->writeln('');
    }
}