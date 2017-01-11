<?php
/**
 * Created by PhpStorm.
 * User: philip
 * Date: 8/3/16
 * Time: 9:48 AM
 */

namespace App\Services;

use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;

/**
 * Class Audit
 *
 * Designed specifically to log audit messages to API. We have no use for other methods - they are there to
 *    prevent issues when calling ->audit()
 *
 * @package App\Services
 */
class NewRelicLogger extends AbstractLogger
{

    private $catchList = array(
        LogLevel::EMERGENCY,
        LogLevel::ALERT,
        LogLevel::CRITICAL,
        LogLevel::ERROR,
        LogLevel::WARNING,
    );

    public function log($level, $message, array $context = array())
    {
        // We only care about a subset of messages.
        if ( ! in_array($level, $this->catchList) ) {
            return false;
        }

        if (extension_loaded ('newrelic')) {
            newrelic_notice_error($message);
        }
    }
}