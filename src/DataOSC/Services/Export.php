<?php
/**
 * Created by PhpStorm.
 * User: philip
 * Date: 12/6/16
 * Time: 11:20 AM
 */

namespace App\DataOSC\Services;

use App\Services\AbstractService;
use App\Services\FlySystemTrait;

class Export extends AbstractService
{

    use FlySystemTrait;

    /**
     * @param array $files
     */
    public function sendToDataOSC(array $files)
    {
        $dirs = $this->getClientConfig()['dirs'];

        $base_dir = realpath(join(DIRECTORY_SEPARATOR, [$dirs['base_dir'], $dirs['export_dir']]));
        $this->getLogger()->debug('Base Directory: ' . $base_dir);

        foreach ($files as $file) {
            if (!$fullpath = realpath(join(DIRECTORY_SEPARATOR, [$base_dir, $file]))) {
                $this->getLogger()->critical(sprintf('Unable to locate file "%s" in directory "%s".', $file,
                    $base_dir));
                continue;
            }

            if ($this->pushFileToDataOSC($file, fopen($fullpath, 'r'))) {
                $this->getLogger()->info(sprintf('"%s" successfully pushed to Commands.', $file));
                $this->moveToCompleted($file);
            } else {
                $this->getLogger()->critical(sprintf('"%s" FAILED to be pushed to Commands.', $file));
            }
        }
    }

    /**
     * @param $file
     * @param $contents
     * @return bool
     * @internal param $cfg
     */
    public function pushFileToDataOSC($file, $contents)
    {
        $cfg = $this->getClientConfig();

        $sftp = $this->getSftpService();
        $this->getLogger()->info(sprintf('Pushing %s to Commands Host %s', $file, $sftp->getAdapter()->getHost()));

        $r = $sftp->writeStream(join(DIRECTORY_SEPARATOR, [$cfg['remote_dirs']['orders_export_dir'], $file]), $contents);
        fclose($contents);

        return $r;
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