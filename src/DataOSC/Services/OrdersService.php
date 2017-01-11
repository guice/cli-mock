<?php
/**
 * Created by PhpStorm.
 * User: philip
 * Date: 12/7/16
 * Time: 10:25 AM
 */

namespace App\DataOSC\Services;


use App\DataOSC\Repository\OrdersRepository;
use App\Services\AbstractService;

class OrdersService extends AbstractService
{

    public function extractOrders($status, $limit, $since = null)
    {
        // TODO: Respository really need to be intellegent on endpoint location
        $endpoints = $this->getConfig()['endpoints'];

        $params = [
            'status' => $status,
            'range'  => ['from' => $since],
            'limit'  => $limit,
            'offset' => 0,
        ];

        foreach (['modified', 'created'] as $modified) {
            // We do have an issue where 'modified' could be NULL. We need to search against 'modified' AND 'created'
            //  NOTE: Idea on issue: an order that's 'cancelled' should always have been 'modified' to 'cancel' status - but isn't
            $params['range']['field'] = $modified;

            do {
                $data = $this->getOrderRepo()->searchOrders($endpoints['search'], $params);
                $pagination = $data->pagination;

                foreach ($data->data as $order) {

                    $user = $this->getOrderRepo()->getUserDetails($order->user_id);
                    $details = $this->getOrderRepo()->getOrderDetails($order->id);
                    $details->notes = $this->getOrderRepo()->getOrderNotes($order->id);
                    $responses = $this->getOrderRepo()->getOrderResponses($details->order_line_id);

                    $details->affiliate_name = $order->affiliate_name; // This is the list view, but not properly linked for details

                    $this->getExtractOrdersService()->extract($user, $details, $responses);

                    /*
                     * TODO: getResponses
                     * Extra row(s) into filesystem CVS -- is it possible to append to gzip? password protected gzip?
                     */
                }

                $params['offset'] += $params['limit'];
            } while ($pagination->limit + $pagination->offset < $pagination->total);
        }

        return $this->getExtractOrdersService()->finalize(); // Closes and zips file. Returns filename of archive
    }

    /**
     * @return OrdersRepository
     */
    protected function getOrderRepo()
    {
        return $this->c['dataOSC.repository.orders'];
    }

    /**
     * @return ExtractOrders
     */
    protected function getExtractOrdersService()
    {
        return $this->c['dataOSC.service.extract_orders'];
    }


}