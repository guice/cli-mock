<?php
/**
 * Created by PhpStorm.
 * User: philip
 * Date: 8/3/16
 * Time: 9:48 AM
 */

namespace App\Services;

use Psr\Log\LoggerInterface;

/**
 * Class Audit
 *
 * Designed specifically to log audit messages to API. We have no use for other methods - they are there to
 *    prevent issues when calling ->audit()
 *
 * @package App\Services
 */
class AuditLogger extends AbstractService implements LoggerInterface
{

    const AUDIT = 'audit';
    const ENDPOINT = '/audits/cli';

    public function audit($message, array $context = array())
    {
        $this->log(self::AUDIT, $message, $context);
    }

    public function log($level, $message, array $context = array())
    {
        /** @var Request $request */
        $request = $this->c['service.request'];

        $uri = $this->getClientConfig()['apis']['api'];

        $endpoint = array_key_exists('endpoint', $context) ? $context['endpoint'] : self::ENDPOINT;
        $context['message'] = $message;

        $response = $request->post($uri . $endpoint, [
            'authenticate' => true,
            'json' => $context,
        ]);

        if ( $response->getStatusCode() != 201 ) {
            throw new Exception(sprintf('API Endpoint "%s" returned "%d" expected 201: "%s"', ($uri . $endpoint),
                $response->getStatusCode(), (string) $response->getBody()));
        }

    }

    // Here solely for the purpose of LoggerInterface
    public function emergency($message, array $context = array())
    {
        return false;
    }

    public function alert($message, array $context = array())
    {
        return false;
    }

    public function critical($message, array $context = array())
    {
        return false;
    }

    public function error($message, array $context = array())
    {
        return false;
    }

    public function warning($message, array $context = array())
    {
        return false;
    }

    public function notice($message, array $context = array())
    {
        return false;
    }

    public function info($message, array $context = array())
    {
        return false;
    }

    public function debug($message, array $context = array())
    {
        return false;
    }

}