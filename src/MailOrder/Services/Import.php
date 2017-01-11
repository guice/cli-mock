<?php
/**
 * Created by PhpStorm.
 * User: philip
 * Date: 7/20/16
 * Time: 3:58 PM
 */

namespace App\MailOrder\Services;


use App\MailOrder\Model\FulfillmentLog;
use App\MailOrder\Model\FulfillmentResult;
use App\Services\AbstractService;
use App\Services\FlySystemTrait;

class Import extends AbstractService
{

    use FlySystemTrait;

    /**
     * @return array
     */
    public function checkForFiles()
    {
        $logger = $this->c['service.logger'];
        $logger->info('Checking for new files to parse.');

        $fs = $this->getFileSystemService();

        $files = [];
        foreach ($fs->listContents($this->getClientConfig()['dirs']['processing_dir']) as $f) {
            // Every file has an .OFS and .LOG - chopping off ending and using just basename.
            if ($f['type'] == 'file') {
                $files[pathinfo($f['basename'], PATHINFO_FILENAME)] = true;
            }
        }

        return array_keys($files);
    }

    public function readIncomingDir()
    {
        $logger = $this->c['service.logger'];
        $logger->info('Checking for new files to parse.');

        $fs = $this->getFileSystemService();

        $files = [];
        foreach ($fs->listContents($this->getClientConfig()['dirs']['incoming_dir']) as $f) {
            if ($f['type'] == 'file') {
                $files[] = $f['basename'];
            }
        }

        return $files;
    }

    public function retrieveFromMailOrder()
    {
        $sftp = $this->getSftpService();
        $lfs = $this->getFileSystemService();

        $base_path = $lfs->getAdapter()->getPathPrefix();
        $import_dir = realpath(join(DIRECTORY_SEPARATOR,
            [$base_path, $this->getClientConfig()['dirs']['incoming_dir']]));

        $downloaded = [];

        foreach ($sftp->listContents($this->getClientConfig()['remote_dirs']['import_dir']) as $f) {
            try {
                // This is to catch any errors from file_put_contents.
                set_error_handler(function ($errno, $errstr, $errfile, $errline) use ($f) {
                    switch ($errno) {
                        // Handle these non-fatal errors as we normally do
                        case E_STRICT:
                        case E_DEPRECATED:
                        CASE E_USER_DEPRECATED:
                        case E_USER_NOTICE:
                        case E_NOTICE:
                            $this->getLogger()->critical(sprintf('%s in file %s on line %d', $errstr, $errfile,
                                $errline));
                            break;
                        default:
                            // Anything WARNING and above
                            throw new Exception(sprintf('file_put_contents FAILED to write "%s": %s', $f['basename'],
                                $errstr), $errno);
                            break;
                    }
                });

                if (file_put_contents(join(DIRECTORY_SEPARATOR, [$import_dir, $f['basename']]),
                        $sftp->readStream($f['path'])) === false
                ) {
                    continue;
                }

                restore_error_handler();

                $sftp->rename($f['path'], join(DIRECTORY_SEPARATOR,
                    [$this->getClientConfig()['remote_dirs']['archive_dir'], $f['basename']]));

                $downloaded[] = $f['filename'];
                $message = sprintf('Downloaded "%s" from MailOrder host "%s"', $f['basename'],
                    $sftp->getAdapter()->getHost());
                $this->getLogger()->info($message);

                if ($f['extension'] == FulfillmentLog::EXTENSION) {
                    continue; // This is a log file: will be post processed
                } elseif ($f['extension'] == FulfillmentResult::EXTENSION) {
                    // Need to audit when Result files are downloaded.
                    $this->getLogger()->audit($message, [
                        'guid'      => $f['basename'],
                        'action_cd' => 'MailOrder_FILE_DOWNLOADED',
                    ]);
                }
            } catch (\Exception $e) {
                // We're looking over an SFTP list: we don't really want to die if _one_ file fails.
                $this->getLogger()->critical($e);
                continue;
            }
        }

        return $downloaded;
    }

    /**
     * @param $file
     * @return bool
     */
    public function moveToCompleted($file)
    {
        $this->getLogger()->info(sprintf('Moving %s to completed directory.', $file));
        $dirs = $this->getClientConfig()['dirs'];

        return $this->getFileSystemService()->rename(join(DIRECTORY_SEPARATOR, [$dirs['incoming_dir'], $file]),
            join(DIRECTORY_SEPARATOR, [$dirs['incoming_dir'], $dirs['completed_dir'], $file]));
    }
}