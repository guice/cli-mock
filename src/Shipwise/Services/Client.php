<?php

namespace GP\Shipwise\Service;

use Zend\Http\Client as ZendClient;
use Zend\Http\Client\Adapter\Exception\TimeoutException;
use Zend\Http\Response;
use Zend\Http\Request;

class Client extends ZendClient
{
    /**
     * @var Client
     */
    protected static $client;

    public function __construct($c)
    {
        parent::__construct(null, ['adapter' => 'Zend\Http\Client\Adapter\Curl']);
        self::$client = $this;
    }

    /**
     * Wrapper method for quick simple POST requests to Node services, using REST-ish implementation.
     *
     * @param $uri
     * @param $args
     * @return mixed
     */
    public static function post($uri, $args)
    {
        self::$client->reset();
        self::$client->getRequest()->getPost()->fromArray($args);
        $response = self::$client->setMethod(Request::METHOD_POST)
            ->setUri($uri)
            ->send();

        return self::validateResponse($response);
    }

    /**
     * Wrapper method for quick simple GET requests to Node services, using REST-ish implementation.
     *
     * @param $uri
     * @param $args
     * @return mixed
     */
    public static function get($uri, $args)
    {
        self::$client->reset();
        self::$client->getRequest()->getQuery()->fromArray($args);
        $response = self::$client->setMethod(Request::METHOD_GET)
            ->setUri($uri)
            ->send();

        return self::validateResponse($response);
    }

    /**
     * Validates response returned by .NET or Node services.
     *
     * @param Response $response
     * @return mixed ->o value returned from service.
     * @throws ServiceException
     */
    protected static function validateResponse(Response $response)
    {
        $return = json_decode($response->getContent());

        if (empty($return) || !$return instanceof \stdClass) {
            throw new ServiceException('Failed to parse service response: ' . $response->getContent());
        }

        return $return ?: new \stdClass();
    }

    /**
     * Sends request to service, whether Node or .NET
     *
     * @param Request $request
     * @return Response
     * @throws TimeoutException
     * @throws \Exception
     */
    public function send(Request $request = null)
    {

        // Zend\Client is returning back horrible binary data for some unknown reason. Coding my own curl for now.

        $c = curl_init();
        $params = $this->getRequest()->getQuery();

        curl_setopt_array($c, [
            CURLOPT_URL => $this->getUri() . '?' . http_build_query($params),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 5,
            CURLOPT_HEADER => 1,
        ]);

        $response = new Response();
        $response = $response->fromString(curl_exec($c));

        return $response;


//        $start = microtime(true);
//        try {
//            var_dump(['Uri' => (string)$this->getUri(), 'GET' => $this->getRequest()->getQuery(), 'POST' => $this->getRequest()->getPost()]);
//            $response = parent::send($request);
//        } catch (TimeoutException $e) {
//            throw new TimeoutException($e->getMessage() . ' on ' . $this->getUri(), $e->getCode());
//        } catch (\Exception $e) {
//            \var_dump(['Uri' => $this->getUri(), 'Return' => $e->getMessage()]);
//            throw $e;
//        }
//
//        \var_dump($response, sprintf('[%d] %s (%0.5f)', $this->getResponse()->getStatusCode(), (string)$this->getUri(), (microtime(true) - $start)));
//        return $response;
    }
}