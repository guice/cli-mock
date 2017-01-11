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
 * @property string Priority
 * @property string RxNumber
 * @property string OrderDate
 * @property int    PatientId
 * @property string PatientFirstName
 * @property string PatientMiddleName
 * @property string PatientLastName
 * @property string PatientGender
 * @property string PatientLanguage
 * @property string PatientDateOfBirth
 * @property string PatientAddressLine1
 * @property string PatientAddressLine2
 * @property string PatientCity
 * @property string PatientState
 * @property string PatientZipcode
 * @property string PatientPhone
 * @property string PrefShipMethod
 * @property string PrescriberName
 * @property string PrescriberPhone
 * @property string ProductType
 * @property string ProductIdentifier
 * @property int    QtyOrdered
 * @property string DrugLabelName
 * @property string Directions
 * @property string CurrentFillNum
 * @property string RefillsLeft
 * @property string QtyLeft
 * @property string RxExpirationDate
 * @property string RefillAfterDate
 * @property string DiscardAfterDate
 * @property int    PlanId
 * @property string PlanName
 * @property string PatientPayAmount
 * @property string CoPay
 * @property string PlanPaidAmount
 * @property double Price
 * @property double UnitCost
 * @property string Comments
 * @property string AdditionalInfo1
 * @property string PharmacyName
 * @property string PharmacyPhone
 * @property string PharmacyAddressLine1
 * @property string PharmacyAddressLine2
 * @property string PharmacyCity
 * @property string PharmacyState
 * @property string PharmacyZipcode
 * @property string GenericFor
 * @property string Manufacturer
 * @property string DAWCode
 * @property string AdditionalInfo2
 * @property string PharmacyText
 * @property string EntryOp
 * @property string Filename  Injected in by _parse method
 */
class Fulfillment extends AbstractModel
{
    const EXTENSION = 'ORF';

    protected function getFieldSequence()
    {
        return array(
            'OrderNumber',
            'Priority',
            'RxNumber',
            'OrderDate',
            'PatientId',
            'PatientFirstName',
            'PatientMiddleName',
            'PatientLastName',
            'PatientGender',
            'PatientLanguage',
            'PatientDateOfBirth',
            'PatientAddressLine1',
            'PatientAddressLine2',
            'PatientCity',
            'PatientState',
            'PatientZipcode',
            'PatientPhone',
            'PrefShipMethod',
            'PrescriberName',
            'PrescriberPhone',
            'ProductType',
            'ProductIdentifier',
            'QtyOrdered',
            'DrugLabelName',
            'Directions',
            'CurrentFillNum',
            'RefillsLeft',
            'QtyLeft',
            'RxExpirationDate',
            'RefillAfterDate',
            'DiscardAfterDate',
            'PlanId',
            'PlanName',
            'PatientPayAmount',
            'CoPay',
            'PlanPaidAmount',
            'Price',
            'UnitCost',
            'Comments',
            'AdditionalInfo1',
            'PharmacyName',
            'PharmacyPhone',
            'PharmacyAddressLine1',
            'PharmacyAddressLine2',
            'PharmacyCity',
            'PharmacyState',
            'PharmacyZipcode',
            'GenericFor',
            'Manufacturer',
            'DAWCode',
            'AdditionalInfo2',
            'PharmacyText',
            'EntryOp',
        );
    }

}