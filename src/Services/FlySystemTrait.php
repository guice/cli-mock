<?php
/**
 * Created by PhpStorm.
 * User: philip
 * Date: 9/8/16
 * Time: 4:23 PM
 */

namespace App\Services;


use App\Lib\Strings;
use League\Csv\Writer;
use League\Flysystem\Filesystem;

trait FlySystemTrait
{

    /**
     * @return Filesystem
     */
    protected function getZipArchiveService($zip_archive)
    {
        /** @var Filesystem $fs */
        $this->getContainer()['service.ziparchive.adapter.cfg'] = $zip_archive;

        return $this->getContainer()['service.ziparchive'];
    }

    /**
     * @return Filesystem
     */
    protected function getSftpService()
    {
        $sftp_cfg = $this->getConfig()['sftp'];
        $sftp_cfg['password'] = Strings::simple_decrypt($sftp_cfg['password']);

        $this->getContainer()['service.sftp.adapter.cfg'] = $sftp_cfg;

        /** @var Filesystem $sftp */
        $sftp = $this->getContainer()['service.sftp'];

        return $sftp;
    }

    /**
     * @return Filesystem
     */
    protected function getFileSystemService()
    {
        /** @var Filesystem $fs */
        $this->getContainer()['service.fs.adapter.cfg'] = $this->getClientConfig()['dirs']['base_dir'];

        return $this->getContainer()['service.fs'];
    }

    /**
     * @param $file
     * @return Writer
     */
    protected function createCsvWriter($file)
    {
        /** @var Writer $csv_class */
        $csv_class = $this->getContainer()['service.csv.writer'];

        return $csv_class::createFromPath($file, "w");
    }
}