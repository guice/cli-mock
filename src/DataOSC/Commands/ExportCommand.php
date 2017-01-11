<?php

namespace App\DataOSC\Commands;

use App\Lib\Strings;
use App\DataOSC\Services\Export;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ExportCommand extends AbstractCommand
{
    public function configure()
    {
        $this->setName('export')->setDescription('Pushes the supplied file to Commands.')
            ->addOption('system', 's', InputOption::VALUE_OPTIONAL, 'Client System Code', 'pp')
            ->addArgument('archives', InputArgument::IS_ARRAY, 'Zip archive(s) to push to Commands');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->c['symfony.outputInterface'] = $output;

        /** @var Export $export */
        $export = $this->c['dataOSC.service.export']->setSystem($input->getOption('system'));

        if ($files = $input->getArgument('archives')) {
            $valid_files = [];

            $this->getLogger()->debug(sprintf('Files: %s', \GuzzleHttp\json_encode($files)));

            foreach ($files as $zip_file) {
                if (!preg_match('!\.(zip)$!i', $zip_file)) {
                    $this->getLogger()
                        ->warning(sprintf('Only pushing encrypted zip archives, skipping %s.',
                            $zip_file));
                } else {
                    $valid_files[] = $zip_file;
                }
            }

            $export->sendToDataOSC($valid_files);
        }
    }
}