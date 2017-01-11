<?php

namespace App\Services;

use GuzzleHttp\Exception\RequestException;

class User extends AbstractService
{
    public function getUser($user_id)
    {
        /** @var Request $request */
        $request = $this->c['service.request'];

        $uri = $this->getClientConfig()['apis']['api'];
        $endpoint = str_replace(':user_id', $user_id, $this->getConfig()['endpoints']['user_details']);

        $this->getLogger()->debug('Calling Endpoint: ' . $uri . $endpoint);
        try {
            $response = $request->get($uri . $endpoint, ['authenticate' => true]);
            return \GuzzleHttp\json_decode((string)$response->getBody());
        } catch (RequestException $e) {
            throw new \Exception('Unable to get user with id ' . $user_id, null, $e);
        }
    }
}
