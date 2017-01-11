<?php

namespace App\DataOSC\Commands;

use App\DataOSC\Services\OrdersService;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ExtractCommand extends AbstractCommand
{
    public function configure()
    {
        $this->setName('extract')->setDescription('Extracts cancelled orders from the last X days for Commands Upload.')
            ->addOption('system', 's', InputOption::VALUE_OPTIONAL, 'Client System Code', 'pp')
            ->addOption('days', 'd', InputOption::VALUE_OPTIONAL, 'Number of days to go back.', 7);
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->c['symfony.outputInterface'] = $output;

        /** @var OrdersService $orderService */
        $orderService = $this->c['dataOSC.service.orders']->setSystem($input->getOption('system'));

        // Conversion of 7am for number of days:
        $date = strtotime('-' . $input->getOption('days') . 'days');
        $time = strtotime(date('m/d/Y 07:00:00', $date));

        $this->getLogger()->debug(sprintf('Parsed date "%s" for timestamp %s - converted: %s', $date, $time,
            date('r', $time)));


        if ($zip_archive = $orderService->extractOrders([9.2, 10], 5, $time)) {
            $this->getLogger()->info(sprintf('Archive created: %s', $zip_archive));

            $command = $this->getApplication()->find('export');

            $arguments = array(
                '--system'   => $input->getOption('system'),
                'archives' => [$zip_archive],
            );

            $args = new ArrayInput($arguments);
            $this->getLogger()->debug(sprintf('Calling "%s" command with arguments: --system=%s %s', $command->getName(), $arguments['--system'],
                join(' ', $arguments['archives'])));
            $command->run($args, $output);
        } else {
            $this->getLogger()->info('No orders found export.');
        }

        return true;
    }
}