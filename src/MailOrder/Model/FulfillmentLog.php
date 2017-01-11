<?php
/**
 * Created by PhpStorm.
 * User: philip
 * Date: 9/7/16
 * Time: 4:56 PM
 */

namespace App\MailOrder\Model;

/**
 * Class FulfillmentResult
 * @package App\Model
 *
 * @property int    OFSLineNumber
 * @property string Status
 * @property string Reason
 * @property string Filename  Injected in by _parse method
 */
class FulfillmentLog extends AbstractModel
{
    const EXTENSION = 'LOG';

    const STATUS_SUCCESS = 'SUCCESS';
    const STATUS_ERROR = 'ERROR';
    const STATUS_EXCEPTION = 'EXCEPTION';

    const VALID_STATUSES = [self::STATUS_SUCCESS, self::STATUS_ERROR];

    protected function getFieldSequence()
    {
        return array(
            'OFSLineNumber',
            'Status',
            'Reason',
        );
    }

    public function setStatus($status)
    {
        if (!in_array($status, self::VALID_STATUSES)) {
            throw new \Exception(sprintf('"%s" is not within valid statuses: %s', $status,
                join(', ', self::VALID_STATUSES)));
        }
        $this->Status = $status;
    }

    public function validate()
    {
        if ($this->Status !== self::STATUS_SUCCESS) {
            throw new \Exception(sprintf('Order failed with status "%s"', $this->Reason));
        }
    }
}