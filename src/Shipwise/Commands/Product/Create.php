<?php
/**
 * Warehouse.php
 *
 * User: Philip G
 * Date: 10/20/15
 */

namespace GP\Shipwise\Command\Product;


use GP\Shipwise\Service\Sqlite;
use Pimple\Container;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use GP\Shipwise\Model\Product;
use GP\Shipwise\Model\Warehouse;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

class Create extends Command
{
    /**
     * @var Container
     */
    protected $c;

    public function __construct($c)
    {
        $this->c = $c;
        parent::__construct('Product');
    }

    protected function configure()
    {
        $this
            ->setName('product:create')
            ->setDescription('Create a new product and assign it to a warehouse');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');

        $args = [];
        $args['name'] = $helper->ask($input, $output, new Question('Name of Product: '));
        $args['dimensions'] = $helper->ask($input, $output, new Question('Dimensions: '));
        $args['weight'] = $helper->ask($input, $output, new Question('Weight: '));

        /** @var Sqlite $s */
        $s = $this->c['Sqlite'];
        $product = Product::create($args, $this->c);
        $s->save($product);

        if ( $helper->ask($input, $output, new ConfirmationQuestion('Would you like to add this it a warehouse? (y/N) ', false, '/^(y|j)/i') )) {
            $warehouses = Warehouse::getWarehouseList($this->c);

            $wh = $helper->ask($input, $output, new ChoiceQuestion(
                "Choose a warehouse to assign '{$product->name}' to:",
                array_keys($warehouses)
            ));

            $warehouse = $warehouses[$wh];

            $quantity = $helper->ask($input, $output, new Question('Quantity: '));
            $product->assignTo($warehouse, $quantity);

            $response = sprintf('Product "%s"x%d (%s) has been created and added to warehouse "%s."',
                $product->name, $quantity, $product->id, $warehouse->name);
        } else  {
            $response = sprintf('Product "%s (%s)" has been created',
                $product->name, $product->id);
        }

        $output->writeln($response);
    }
}