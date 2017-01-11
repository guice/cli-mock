<?php
/**
 * Created by PhpStorm.
 * User: philip
 * Date: 7/20/16
 * Time: 2:49 PM
 */

namespace App\MailOrder\Commands;

use App\MailOrder\Services\Export;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ExportCommand extends AbstractCommand
{
    public function configure()
    {
        $this->setName('export')
            ->setDescription('Pushes supplied ORS file to MailOrder. If not file is supplied, it will poll the export directory for new files to push.')
            ->addOption('system', 's', InputOption::VALUE_OPTIONAL, 'Client System Code', 'pp')
            ->addArgument('export.orf', InputArgument::IS_ARRAY, 'ORF file to push to MailOrder');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->c['symfony.outputInterface'] = $output;
        /** @var LoggerInterface $logger */
        $logger = $this->c['service.logger'];

        /** @var Export $export */
        $export = $this->c['mailOrder.service.export']->setSystem($input->getOption('system'));

        if ($files = explode(' ', $input->getArgument('export.orf'))) {
            $valid_files = [];

            foreach ( $files as $orf_file ) {
                if (!preg_match('!\.(orf|log)$!i', $orf_file)) {
                    $logger->warning(sprintf('MailOrder ORF file "%s" must end with .orf extension. Skipping...',
                        $orf_file));
                } else {
                    $valid_files[] = $orf_file;
                }
            }

            $export->sendToMailOrder($valid_files);
        }
    }

}