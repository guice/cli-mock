<?php
/**
 * Created by PhpStorm.
 * User: philip
 * Date: 12/6/16
 * Time: 12:15 PM
 */

namespace App\DataOSC\Model;


use App\Lib\BaseObject;

/**
 * Class ExtractRow
 * @package App\Commands\Model
 *
 * @property string PatientID
 * @property string PatientLastName
 * @property string PatientFirstName
 * @property string PatientStreet
 * @property string PatientCity
 * @property string PatientState
 * @property string PatientZip
 * @property string Phone
 * @property string Gender
 * @property string DOB
 * @property string VisitID
 * @property string VisitDate
 * @property string Location
 * @property string VisitReason
 * @property string QuestionID
 * @property string Question
 * @property string Answer
 * @property string AnswerText
 * @property string Assessment
 * @property string Treatment
 * @property string Medication
 * @property string Dosage
 * @property string Pharmacy
 * @property string PharmacyStreet
 * @property string PharmacyCity
 * @property string PharmacyState
 * @property string PharmacyZip
 * @property string Physician
 * @property string NPI
 */
class ExtractRow
{
    use BaseObject; // Imports base __set, __get calls


    public function __construct()
    {
        $this->_setDefaults();
    }

    protected function getFieldSequence()
    {
        return array(
            'PatientID',
            'PatientLastName',
            'PatientFirstName',
            'PatientStreet',
            'PatientCity',
            'PatientState',
            'PatientZip',
            'Phone',
            'Gender',
            'DOB',
            'VisitID',
            'VisitDate',
            'Location',
            'VisitReason',
            'QuestionID',
            'Question',
            'Answer',
            'AnswerText',
            'Assessment',
            'Treatment',
            'Medication',
            'Dosage',
            'Pharmacy',
            'PharmacyStreet',
            'PharmacyCity',
            'PharmacyState',
            'PharmacyZip',
            'Physician',
            'NPI',
        );
    }

    protected function _setDefaults() {
        foreach ($this->getFieldSequence() as $field) {
            $this->$field = "";
        }
    }

}