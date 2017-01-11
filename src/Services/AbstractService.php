<?php
/**
 * Created by PhpStorm.
 * User: philip
 * Date: 7/21/16
 * Time: 12:10 PM
 */

namespace App\Services;

use App\Middleware\Logger;
use Pimple\Container;

abstract class AbstractService
{
    /**
     * @var Container
     */
    protected $c;

    /** @var  string System code used by Requests */
    protected static $system;

    protected $client_config;

    public function __construct(Container $c)
    {
        $this->c = $c;
    }

    /**
     * Returns global app configuration.
     *
     * @return array
     */
    public function getConfig()
    {
        return $this->c['config'];
    }

    /**
     * Returns client-specific configurations values, or 'default` is no SystemCode is set
     *
     * @return array
     */
    public function getClientConfig()
    {
        $logger = $this->c['service.logger'];

        $cfg = $this->getConfig()['clients'];
        if (self::$system && array_key_exists(self::$system, $cfg)) {
            return $this->client_config = $cfg[self::$system];
        } else {
            $logger->debug(sprintf('Undefined or Unknown system code "%s". Setting clientConfig to "default".',
                strtoupper(self::$system)));

            return $cfg['default'];
        }
    }

    public function setSystem($system)
    {
        $logger = $this->c['service.logger'];
        $logger->info('Setting System Code: ' . strtoupper($system));

        self::$system = strtolower($system);

        return $this;
    }

    /**
     * @return Logger
     */
    public function getLogger()
    {
        $logger = $this->c['service.logger'];

        return $logger;
    }

    /**
     * @return \App\Services\Request
     */
    public function getRequestService()
    {
        return $this->c['service.request'];
    }

    /**
     * @return Container
     */
    public function getContainer() {
        return $this->c;
    }
}