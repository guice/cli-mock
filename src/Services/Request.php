<?php
/**
 * Created by PhpStorm.
 * User: philip
 * Date: 7/28/16
 * Time: 11:54 AM
 */

namespace App\Services;

use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;

/**
 * @method ResponseInterface get($uri, array $options = [])
 * @method ResponseInterface head($uri, array $options = [])
 * @method ResponseInterface put($uri, array $options = [])
 * @method ResponseInterface post($uri, array $options = [])
 * @method ResponseInterface patch($uri, array $options = [])
 * @method ResponseInterface delete($uri, array $options = [])
 *
 * @package App\Services
 */
class Request extends AbstractService
{
    protected $auth_token;

    protected $headers = [
        'Accept' => 'application/json',
    ];

    protected function authenticate()
    {
        if (empty($this->auth_token)) {
            $logger = $this->c['service.logger'];
            $logger->debug('Generating new Authentication Token');

            $login = $this->getConfig()['svc_login']; // PHP 5.6 only
            $uri = $this->getClientConfig()['apis']['api'];
            $response = $this->post($uri . '/users/me/authenticate', ['json' => $login]);

            if ($response->getStatusCode() == 200) {
                $this->headers['Authorization'] = \GuzzleHttp\json_decode((string)$response->getBody())->token;
                $logger->debug('New Authentication Token: '  . $this->headers['Authorization']);
            }
        }

        return $this;
    }

    /**
     * @return Client
     */
    private function getClient()
    {
        return $this->c['service.guzzle'];
    }


    public function __call($method, $args)
    {
        /** @var ConsoleLogger $logger */
        $logger = $this->c['service.logger'];
        if (count($args) < 1) {
            throw new \InvalidArgumentException('Magic request methods require a URI and optional options array');
        }

        $uri = $args[0];
        $opts = isset($args[1]) ? $args[1] : [];

        // Addes in Authentication header
        if (isset($opts['authenticate']) && $opts['authenticate'] && !array_key_exists('Authorization',
                $this->headers)
        ) {
            $logger->debug('Service requires authentication');
            $this->authenticate();
        }

        // X-Service/System code used by APIs
        $this->headers['X-System-Code'] = $this->headers['X-Service-Code'] = strtoupper(static::$system) ?: 'PD';

        $opts['headers'] = $this->headers;
//        $opts['debug'] = $this->c['symfony.outputInterface']->isDebug();

        return $this->getClient()
            ->request($method, $uri, $opts);
    }
}