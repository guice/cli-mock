<?php
/**
 * Created by PhpStorm.
 * User: philip
 * Date: 7/20/16
 * Time: 2:49 PM
 */

namespace App\MailOrder\Commands;

use App\MailOrder\Model\FulfillmentLog;
use App\MailOrder\Model\FulfillmentResult;
use App\MailOrder\Services\Import;
use App\MailOrder\Services\Transactions;
use League\Csv\Writer;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ProcessCommand extends AbstractCommand
{

    public function configure()
    {
        $this->setName('process')->setDescription('Processes files located within the "processing" directory.')
            ->addOption('system', 's', InputOption::VALUE_OPTIONAL, 'Client System Code', 'pp');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->c['symfony.outputInterface'] = $output;
        $logger = $this->c['service.logger'];

        /** @var Import $import */
        $import = $this->c['mailOrder.service.import']->setSystem($input->getOption('system'));
        $files = $import->readIncomingDir();

        /** @var Transactions $transaction */
        $transaction = $this->c['mailOrder.service.transactions'];

        if ($files) {
            $logger->info(sprintf('Located %d Files ready to be processed.', count($files)));

            foreach ($files as $file) {
                $extension = pathinfo($file, PATHINFO_EXTENSION);
                try {
                    switch (strtoupper($extension)) {
                        case FulfillmentLog::EXTENSION:
                            $transaction->parseLogFile($file);
                            break;
                        case FulfillmentResult::EXTENSION:
                            $fulfillments = $transaction->parseTransactionFile($file);
                            $log = $transaction->update($fulfillments);
                            $transaction->exportLog($file, $log);
                            break;
                        default:
                            throw new \Exception(sprintf('Unknown file extension %s', $extension));
                    }
                } catch (\Exception $e) {
                    // If we can't parse the file; raise critical and continue to the next
                    $logger->critical($e);
                    continue;
                }

                $import->moveToCompleted($file);
            }
        } else {
            $logger->info('No files to process. Exiting.');
        }
    }
}