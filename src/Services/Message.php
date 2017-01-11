<?php

namespace App\Services;

use GuzzleHttp\Exception\RequestException;

class Message extends AbstractService
{
    public function postMessage($recipient_id, $template_name, $send_at_interval, $object_type, $object_status, $object_id, $attributes)
    {
        /** @var Request $request */
        $request = $this->c['service.request'];

        $uri = $this->getClientConfig()['apis']['api'];
        $endpoint = $this->getConfig()['endpoints']['templated_messages'];

        $payload = array(
            'recipient_id' => $recipient_id,
            'template_name' => $template_name,
            'send_at_interval' => $send_at_interval,
            'object_type' => $object_type,
            'object_status' => $object_status,
            'object_id' => $object_id,
            'attributes' => $attributes
        );

        $this->getLogger()->debug('Calling Endpoint: ' . $uri . $endpoint);
        try {
            $request->post($uri . $endpoint, ['authenticate' => true, 'json' => $payload]);
        } catch (RequestException $e) {
            throw new \Exception('Unable to post ' . $template_name . ' message for ' . $object_type . ' ' . $object_id, null, $e);
        }
    }
}
