<?php
/**
 * GoogleMapsAPI.php
 *
 * User: Philip G
 * Date: 10/20/15
 */

namespace GP\Shipwise\Service;


use Pimple\Container;

class GoogleMapsAPI
{
    const URL = 'https://maps.googleapis.com/maps/api/geocode/json';

    protected $apiKey;

    /**
     * @var Container
     */
    protected $c;

    public function __construct(Container $c)
    {
        $this->c = $c;
        $this->apiKey = $c['config']['googleMaps']['apiKey'];
    }

    public function decode($params)
    {
        $params['key'] = $this->apiKey;
        $client = $this->c['Http\\Client'];

        return $client::get(self::URL, $params);
    }
}