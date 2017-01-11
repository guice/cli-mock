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
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use GP\Shipwise\Model\Warehouse;
use GP\Shipwise\Service\Sqlite;
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
        parent::__construct('Warehouse');
    }

    protected function configure()
    {
        $this
            ->setName('warehouse:create')
            ->setDescription('Create a new warehouse');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');

        $args = [];
        $args['name'] = trim($helper->ask($input, $output, new Question('Name of Warehouse: ')));
        $args['address'] = trim($helper->ask($input, $output, new Question('Full Address: ')));

// So you can see how it did it earlier.
//        $output->write(str_pad('Name of Warehouse: ', 20, ' ', STR_PAD_LEFT));
//        $args['name'] = trim(fgets(STDIN));
//
//        $output->write(str_pad('Address: ', 20, ' ', STR_PAD_LEFT));
//        $args['street'] = trim(fgets(STDIN));

        if  ( $decode = $this->c['GoogleMaps']->decode(['address' => $args['address']]) ) {
            $geo = $decode->results[0]->geometry;

            $args['longitude'] = $geo->location->lng;
            $args['latitude'] = $geo->location->lat;
        }

        /** @var $sqlite Sqlite */
        $sqlite = $this->c['Sqlite'];
        $id = $sqlite->save(Warehouse::create($args, $this->c));

        $output->writeln('New warehouse "'.$args['name'].'" created: ' . $id);
    }
}