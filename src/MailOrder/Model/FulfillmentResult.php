<?php
/**
 * Created by PhpStorm.
 * User: philip
 * Date: 8/8/16
 * Time: 10:30 AM
 */

namespace App\MailOrder\Model;

/**
 * Class FulfillmentResult
 * @package App\Model
 *
 * @property int    OrderNumber
 * @property int    RxNumber
 * @property string DrugLabelName
 * @property int    Quantity
 * @property int    PatientId
 * @property string PatientFirstName
 * @property string PatientMiddleName
 * @property string PatientLastName
 * @property string OrderDate
 * @property string Status
 * @property string TrackingNumber
 * @property string CarrierDesc
 * @property int    ShippingCost
 * @property int    LastModifiedDateTime
 * @property string CancelReason
 * @property string Filename  Injected in by _parse method
 */
class FulfillmentResult extends AbstractModel
{
    const VALID_STATUSES = ['SHIPPED', 'CANCELLED'];
    const EXTENSION = 'ORS';

    protected function getFieldSequence()
    {
        return array(
            'OrderNumber',
            'RxNumber',
            'DrugLabelName',
            'Quantity',
            'PatientId',
            'PatientFirstName',
            'PatientMiddleName',
            'PatientLastName',
            'OrderDate',
            'Status',
            'TrackingNumber',
            'CarrierDesc',
            'ShippingCost',
            'LastModifiedDateTime',
            'CancelReason',
        );
    }

    public function setTrackingNumber($tracking)
    {
        $this->TrackingNumber = $tracking;
    }

    public function setStatus($status)
    {
        if (!in_array($status, self::VALID_STATUSES)) {
            throw new \Exception(sprintf('"%s" is not within valid statuses: %s', $status,
                join(', ', self::VALID_STATUSES)));
        }

        $this->Status = $status;
    }

    public function setOrderDate($date)
    {
        $this->OrderDate = strtotime($date);
    }

    public function validate()
    {
        if ($this->Status !== 'SHIPPED' && $this->TrackingNumber) {
            throw new \Exception(sprintf('Received Tracking number "%s" on status "%s." Expected status "SHIPPED."',
                $this->TrackingNumber, $this->Status));
        }/* else {  // Tracking number may not always be available.
            if ($this->Status === 'SHIPPED' && empty($this->TrackingNumber)) {
                throw new \Exception(sprintf('Status is "%s" with no Tracking Number!', $this->Status));
            }
        }*/
    }
}