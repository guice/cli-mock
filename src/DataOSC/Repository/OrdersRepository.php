<?php
/**
 * Created by PhpStorm.
 * User: philip
 * Date: 12/8/16
 * Time: 5:23 PM
 */

namespace App\DataOSC\Repository;


use App\Middleware\Logger;
use App\Repository\AbstractRepository;
use App\Services\Request;
use GuzzleHttp\Exception\RequestException;

class OrdersRepository extends AbstractRepository
{

    public function getOrderNotes($id)
    {
        $endpoints = $this->getRequestService()->getConfig()['endpoints'];
        $ep = str_replace(':id', $id, $endpoints['order_notes']);

        $this->getLoggerService()->debug('Calling Endpoint: ' . $this->getUri());
        $response = $this->getRequestService()->get($this->getUri() . $ep, [
            'authenticate' => true,
        ]);

        return \GuzzleHttp\json_decode((string)$response->getBody());
    }

    public function getUserDetails($id)
    {
        $endpoints = $this->getRequestService()->getConfig()['endpoints'];
        $ep = str_replace(':id', $id, $endpoints['user_details']);

        $this->getLoggerService()->debug('Calling Endpoint: ' . $this->getUri());
        $response = $this->getRequestService()->get($this->getUri() . $ep, [
            'authenticate' => true,
        ]);

        return \GuzzleHttp\json_decode((string)$response->getBody());
    }

    public function getOrderDetails($id)
    {
        $endpoints = $this->getRequestService()->getConfig()['endpoints'];
        $ep = str_replace(':id', $id, $endpoints['order_details']);

        $this->getLoggerService()->debug('Calling Endpoint: ' . $this->getUri());
        $response = $this->getRequestService()->get($this->getUri() . $ep, [
            'authenticate' => true,
        ]);

        return \GuzzleHttp\json_decode((string)$response->getBody());
    }

    public function getOrderResponses($id)
    {
        $endpoints = $this->getRequestService()->getConfig()['endpoints'];
        $ep = str_replace(':id', $id, $endpoints['responses']);

        $this->getLoggerService()->debug('Calling Endpoint: ' . $this->getUri());
        $response = $this->getRequestService()->get($this->getUri() . $ep, [
            'authenticate' => true,
        ]);

        return \GuzzleHttp\json_decode((string)$response->getBody());
    }

    public function searchOrders($endpoint, array $params = [])
    {
        try {
            $this->getLoggerService()->debug('Calling Endpoint: ' . $this->getUri() . $endpoint . ' Parameters: ' . json_encode($params));
            $response = $this->getRequestService()->post($this->getUri() . $endpoint, [
                'authenticate' => true,
                'json'         => $params,
            ]);
        } catch (RequestException $e) {
            if ($e->getResponse() /* For failed connection checks */ && $e->getResponse()->getStatusCode() == 404) {
                // 404 = No Orders Found
                return false;
            } else {
                throw $e;
            }
        }

        $body = \GuzzleHttp\json_decode((string)$response->getBody());

        return $body;
    }

    public function getUri()
    {
        // This seems odd.
        return $this->getRequestService()->getClientConfig()['apis']['api'];
    }

    /**
     * @return Request
     */
    protected function getRequestService()
    {
        return $this->c['service.request'];
    }

    /**
     * @return Logger
     */
    protected function getLoggerService()
    {
        return $this->c['service.logger'];
    }

}