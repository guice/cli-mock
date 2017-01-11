<?php
/**
 * Created by PhpStorm.
 * User: philip
 * Date: 7/25/16
 * Time: 2:14 PM
 */

namespace App\MailOrder\Commands;


use App\MailOrder\Services\Export;
use App\MailOrder\Services\Transactions;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class PollCommand extends AbstractCommand
{
    public function configure()
    {
        $this->setName('poll')
            ->setDescription('Polls the API for prescriptions ready to be pushed up.')
            ->addOption('system', 's', InputOption::VALUE_OPTIONAL, 'Client System Code', 'pp')
            ->addOption('poll-only', null, InputOption::VALUE_NONE, 'Only poll for local files to push')
            ->addOption('no-export', null, InputOption::VALUE_NONE, 'Debug purposes: stops pushing files to Rideway');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @return bool
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->c['symfony.outputInterface'] = $output;
        $logger = $this->c['service.logger'];

        /** @var Transactions $transaction */
        $transaction = $this->c['mailOrder.service.transactions']->setSystem($input->getOption('system'));

        if (!$input->getOption('poll-only') && $fill_ids = $transaction->checkForNew()) {
            $fill_ids = $transaction->settle($fill_ids); // Creates settlements and returns successful fill_ids
            if ($fill_ids) {
                $file = $transaction->getFileForFills($fill_ids); // This is going to create a file in the export dir
                if ($file) {
                    $logger->audit(sprintf('MailOrder CLI Received File "%s" from API.',  $file), [
                        'guid'     => $file,
                        'action_cd' => 'MailOrder_FILE_GENERATED',
                    ]);
                }
            }
            // - now all we need to do is run polling
        }

        /** @var Export $export */
        $export = $this->c['mailOrder.service.export']->setSystem($input->getOption('system'));
        $files = $export->checkForFiles();

        $this->getLogger()->info(sprintf('Located %d files.', count($files)));
        if (isset($file) && !in_array($file, $files)) {
            // TODO: Something bad happened here.
        }

        if ( $input->getOption('no-export') ) {
            $logger->info(sprintf('Export manually disabled. %d files have been generated.', count($files)));
            return true;
        }

        if (!empty($files)) {
            $logger->info(sprintf('Located %d Files ready to push.', count($files)));

            $command = $this->getApplication()
                ->find('export');

            $arguments = array(
                '--system'   => $input->getOption('system'),
                'export.orf' => join(' ', $files),
            );

            $args = new ArrayInput($arguments);
            $logger->debug(sprintf('Calling "%s" command with arguments: --system=%s %s', $command->getName(), ...
                array_values($arguments)));
            $command->run($args, $output);
        }

        return true;
    }
}