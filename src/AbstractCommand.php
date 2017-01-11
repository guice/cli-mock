<?php
/**
 * Created by PhpStorm.
 * User: philip
 * Date: 7/22/16
 * Time: 11:35 AM
 */

namespace App;

use App\Middleware\Logger;
use Pimple\Container;
use Symfony\Component\Console\Command\Command;

class AbstractCommand extends Command
{
    /**
     * @var Container
     */
    protected $c;

    public function __construct(Container $c)
    {
        $this->c = $c;
        parent::__construct();
    }

    /**
     * @return Logger
     */
    public function getLogger()
    {
        $logger = $this->c['service.logger'];

        return $logger;
    }

}