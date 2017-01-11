<?php
/**
 * Created by PhpStorm.
 * User: philip
 * Date: 7/20/16
 * Time: 2:49 PM
 */

namespace App\MailOrder\Commands;

use App\MailOrder\Services\Import;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ImportCommand extends AbstractCommand
{

    public function configure()
    {
        $this->setName('import')->setDescription('Checks for and pulls ORF files from MailOrder')
            ->addOption('system', 's', InputOption::VALUE_OPTIONAL, 'Client System Code', 'pp')
            ->addOption('parse-only', null, InputOption::VALUE_NONE,
                'Only check and parse local files -- does not hit MailOrder');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->c['symfony.outputInterface'] = $output;
        $logger = $this->c['service.logger'];

        /** @var Import $import */
        $import = $this->c['mailOrder.service.import']->setSystem($input->getOption('system'));

        if (!$input->getOption('parse-only')) {
            $downloads = $import->retrieveFromMailOrder();
        }

        if (isset($downloads)) {
            $command = $this->getApplication()->find('process');

            $arguments = array(
                '--system' => $input->getOption('system'),
            );

            $args = new ArrayInput($arguments);
            $logger->debug(sprintf('Calling "%s" command with arguments: --system=%s', $command->getName(), ...
                array_values($arguments)));
            $command->run($args, $output);
        } else {
            $logger->info('No files downloaded; skipping file processing.');
        }
    }
}