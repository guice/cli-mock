<?php
/**
 * Warehouse.php
 *
 * User: Philip G
 * Date: 10/20/15
 */

namespace GP\Shipwise\Command\Order;


use GP\Shipwise\Model\Order;
use Pimple\Container;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use GP\Shipwise\Model\Product;
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
            ->setName('order:create')
            ->setDescription('Create an new order');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');

        $args = [];
        $args['ship_addr'] = $helper->ask($input, $output, new Question('Shipping Address for Order: '));


        if  ( $decode = $this->c['GoogleMaps']->decode(['address' => $args['ship_addr']]) ) {
            $geo = $decode->results[0]->geometry;

            $args['longitude'] = $geo->location->lng;
            $args['latitude'] = $geo->location->lat;
        }

        $order = Order::create($args, $this->c);

        /** @var $sqlite Sqlite */
        $sqlite = $this->c['Sqlite'];
        $sqlite->save($order);

        $products = Product::getProductList($this->c);

        do {
            $question = new ChoiceQuestion(
                "Choose product(s) to add to order (comma separated): ",
                array_keys($products)
            );
            $question->setMultiselect(true);
            $pr = $helper->ask($input, $output, $question);
            $quantity = $helper->ask($input, $output, new Question('Quantity: '));

            $opp = [];
            foreach ( $pr as $item) {
                $opp[] = $products[$item];
            }

            $order->add($opp, $quantity);
        } while ($helper->ask($input, $output, new ConfirmationQuestion('Add more? (y/N) ', false, '/^(y|j)/i') ));

        $whs = Product::locateStock($order->id ,$this->c);
        list($warehouse, $distance) = $order->assignWarehouse($whs);

        $output->writeln(sprintf('New order "%s" created. Will be shipped from %s (%s). It is only %f distance away.'
            , $order->id, $warehouse->name, $warehouse->id, $distance));
    }
}