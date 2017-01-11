<?php
/**
 * Created by PhpStorm.
 * User: philip
 * Date: 8/29/16
 * Time: 9:32 AM
 */

namespace App\Services;

use League\Flysystem\Sftp\SftpAdapter;
use LogicException;

/**
 * Class SftpAdapter
 *
 * We're going to overload Flysystem's SftpAdapter in order to fix a bug handling key AND password authentication
 *
 * @package App\MailOrder
 */
class DualFactorSftpAdapter extends SftpAdapter
{
    /**
     * Login.
     *
     * Third parties use two factor authentication: key and password. In order for this to work, we have to overload
     *    the login() method to authenticate via key and then via password.
     *
     * I've removed Agent code since for this specific use, we're not using Agent forwarding.
     *
     * @throws LogicException
     */
    protected function login()
    {
        if (! $this->connection->login($this->getUsername(), $this->getPrivateKey())
            && ! $this->connection->login($this->getUsername(), $this->getPassword())) {
            throw new LogicException('Could not login with username: '.$this->getUsername().', host: '.$this->getHost());
        }
    }
}