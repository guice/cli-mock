<?php
/**
 * Created by PhpStorm.
 * User: philip
 * Date: 7/20/16
 * Time: 3:22 PM
 */

namespace App\MailOrder\Services;

use App\Services\AbstractService;
use App\Services\FlySystemTrait;

class Export extends AbstractService
{
    use FlySystemTrait;

    /**
     * @return array
     */
    public function checkForFiles()
    {
        $this->getLogger()->info('Checking for new files to push.');

        $files = [];
        foreach ($this->getFileSystemService()->listContents($this->getClientConfig()['dirs']['export_dir']) as $f) {
            if ($f['type'] == 'file') {
                $files[] = $f['basename'];
            }
        }

        return $files;
    }

    /**
     * @param array $files
     */
    public function sendToMailOrder(array $files)
    {
        $dirs = $this->getClientConfig()['dirs'];

        $base_dir = realpath(join(DIRECTORY_SEPARATOR, [$dirs['base_dir'], $dirs['export_dir']]));
        $this->getLogger()->debug('Base Directory: ' . $base_dir);

        foreach ($files as $file) {
            if (!$fullpath = realpath(join(DIRECTORY_SEPARATOR, [$base_dir, $file]))) {
                $this->getLogger()->critical(sprintf('Unable to locate file "%s" in directory "%s". Ignoring!', $file,
                    $base_dir));
                continue;
            }

            if ($this->pushFileToMailOrder($file, file_get_contents($fullpath))) {
                $this->getLogger()->info(sprintf('"%s" successfully pushed to MailOrder.', $file));
                $this->moveToCompleted($file);
            }
        }
    }

    /**
     * @param $file
     * @param $contents
     * @return bool
     * @internal param $cfg
     */
    public function pushFileToMailOrder($file, $contents)
    {
        $cfg = $this->getClientConfig();

        $sftp = $this->getSftpService();
        $this->getLogger()->info(sprintf('Pushing %s to MailOrder Host', $file));

        $sftp->write(join(DIRECTORY_SEPARATOR, [$cfg['remote_dirs']['export_dir'], $file]), $contents);

        $this->getLogger()->audit(sprintf('%s pushed to MailOrder host %s.', $file,
            $sftp->getAdapter()->getHost()), [
            'guid'      => $file,
            'action_cd' => 'MailOrder_FILE_PUSHED',
        ]);

        return true;
    }

    /**
     * @param $file
     * @return bool
     */
    public function moveToCompleted($file)
    {
        $this->getLogger()->info(sprintf('Moving %s to completed directory.', $file));
        $dirs = $this->getClientConfig()['dirs'];

        return $this->getFileSystemService()->rename(join(DIRECTORY_SEPARATOR, [$dirs['export_dir'], $file]),
            join(DIRECTORY_SEPARATOR, [$dirs['export_dir'], $dirs['completed_dir'], $file]));
    }
}
