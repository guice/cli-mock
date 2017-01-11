<?php

namespace App\DataOSC\Services;

use App\Lib\Strings;
use App\DataOSC\Model\ExtractRow;
use App\Services\AbstractService;
use App\Services\FlySystemTrait;
use League\Csv\Writer;
use PhpZip\Model\ZipEntry;
use PhpZip\ZipOutputFile;

class ExtractOrders extends AbstractService
{

    use FlySystemTrait;
    /**
     * @var Writer
     */
    protected $csv;

    /**
     * @var string
     */
    protected $csv_name;

    // TODO: Add to user properties
    protected $npi_mapping = [
        'Zell'  => '1528465192',
        'Levy'  => '1811208044',
        'Liu'   => '1700964491',
        'Hwang' => '1669490926',
        'Koob'  => '1093943821',
        'Anh'   => '1134440431',
    ];

    public function extract($user, $order, $responses)
    {
        $rows = [];
        if (empty($responses)) {
            $rows[] = (array)$this->_extractRow($user, $order, null, null);
        } else {
            foreach ($responses->answers as $answer) {
                if (is_array($answer->value)) {
                    foreach ($answer->value as $option) {
                        $rows[] = (array)$this->_extractRow($user, $order, $answer, $option);
                    }
                } else {
                    $rows[] = (array)$this->_extractRow($user, $order, $answer, $answer->value);
                }
            }
        }

        $this->getLogger()->debug(sprintf('Total of %d rows extracted for VisitID %d.', count($rows), $order->id));
        $this->exportRows($rows);
    }

    /**
     *
     * @return string
     */
    public function finalize()
    {
        $this->getLogger()->debug(sprintf('Finalizing export'));
        return $this->csv_name ? $this->zipExport($this->csv_name) : null;
    }

    /**
     * @param $user
     * @param $order
     * @param $answer
     * @param $value
     * @return ExtractRow
     * @internal param $medication
     */
    protected function _extractRow($user, $order, $answer, $value)
    {
        $row = new ExtractRow();
        $row->PatientID = $user->id;
        $row->PatientFirstName = $user->firstname;
        $row->PatientLastName = $user->lastname;
        $row->PatientStreet = $user->address->road;
        $row->PatientCity = $user->address->city;
        $row->PatientState = $user->address->state;
        $row->PatientZip = $user->address->zip;
        $row->Phone = $user->phones[0]->number;
        $row->Gender = $user->gender;
        $row->DOB = date('Y-m-d', strtotime($user->dob));
        $row->VisitID = $order->id;
        $row->VisitDate = $order->timestamps->ordered;
        $row->Location = $order->affiliate_name;
        $row->VisitReason = $order->title;
        $row->Assessment = join('; ', array_map(function ($m) {
            return preg_replace("/\n/", "\\n", $m->text);
        }, $order->notes));

        if ($order->status == 10) {
            if (strpos($order->title, 'Birth Control') !== false) {
                $row->Treatment = 'An e-prescription for (12) months of the recommended oral contraceptive pill has been sent to the patient’s selected pharmacy. The patient was provided a link to review the contents of CI/CIIC and detailed information about the prescribed medicine, including instructions for how to self-administer and common and serious side effects. Required written information given. Client demonstrates understanding and is aware of how to obtain the information if needed.';
            } elseif (strstr($order->title, 'UTI') !== false) {
                $row->Treatment = 'An e-prescription for an oral antibiotic has been sent to the patients selected pharmacy.  Information about the prescribed medications, including instructions for how to self-administer and common and serious side effects were provided to the patient.  Must include 5 elements of notification: Reviewed UTI results with patient. Discussed the nature of the findings and implications. Discussed the possible consequences of not receiving additional diagnostic testing or treatment. Discussed the management options. Informed patient that it is her or his responsibility to follow up. All questions addressed. Patient agrees to plan and verbalizes understanding of instructions and agrees to follow up as instructed.';
            } else {
                $row->Treatment = 'N/A';
            }
        }

        if ($order->medications) {
            $row->Medication = join(", ", array_map(function ($m) {
                return $m->name;
            }, $order->medications));
            $row->Dosage = join('\n', array_map(function ($m) {
                return sprintf('(%s) %s', $m->name, $m->dosage);
            }, $order->medications));

            // In the event of multiple medications: it's always done simultaneously by the same doctor. This is safe.
            $row->Physician = $order->medications[0]->prescribed_by->lastname;
            if ($row->Physician && isset($this->npi_mapping[$row->Physician])) {
                $row->NPI = $this->npi_mapping[$row->Physician];
            }
        }

        if (isset($order->delivery->type) && $order->delivery->type != 'home') {
            $row->Pharmacy = $order->delivery->address->name;
            $row->PharmacyStreet = $order->delivery->address->road;
            $row->PharmacyCity = $order->delivery->address->city;
            $row->PharmacyState = $order->delivery->address->state;
            $row->PharmacyZip = $order->delivery->address->zip;
        }

        if (is_object($answer)) {
            $answer_text = is_object($value) ? $value->text : $value;
            if (!empty($answer->detail)) { // For answers that take additional info (e.g. "Other")
                $answer_text .= ": " . $answer->detail;
            }

            $row->QuestionID = $answer->question->id;
            $row->Question = $answer->question->text;
            $row->Answer = is_object($value) ? $value->id : $answer->value;
            $row->AnswerText = $answer_text;
        }

        return $row;
    }

    protected function exportRows(array $rows)
    {
        return $this->_csv()->insertAll($rows);
    }

    protected function zipExport($csv_path)
    {
        $zip_name = join(DIRECTORY_SEPARATOR, [pathinfo($csv_path, PATHINFO_DIRNAME), pathinfo($csv_path, PATHINFO_FILENAME).".zip"]);

        $zip = $this->getZipArchiveService();

        $zip->addFromFile($csv_path, pathinfo($csv_path, PATHINFO_BASENAME));
        $zip->setPassword(Strings::simple_decrypt($this->getConfig()['zip_password']), ZipEntry::ENCRYPTION_METHOD_TRADITIONAL);
        $zip->saveAsFile($zip_name);

        unlink($csv_path); // Remove original upon successful zip

        $this->getLogger()->debug(sprintf('Zipfile %s created.', pathinfo($zip_name, PATHINFO_BASENAME)));
        return pathinfo($zip_name, PATHINFO_BASENAME);
    }

    /**
     * @return \League\Csv\Writer
     */
    protected function _csv()
    {
        if (empty($this->csv)) {
            $csv_name = sprintf('DataOSC__%s.csv', date('F_d', strtotime('-1 day')));

            $dirs = $this->getClientConfig()['dirs'];
            $this->csv_name = join(DIRECTORY_SEPARATOR, [$dirs['base_dir'], $dirs['export_dir'], $csv_name]);

            $this->getLogger()->info(sprintf('Generating %s into %s.', $csv_name, $dirs['export_dir']));
            $this->csv = $this->createCsvWriter($this->csv_name);
        }

        return $this->csv;
    }

    /**
     * league/flysystem-ziparchive does not support creating encrypted zip files. A such, we're required to overload these with
     *    another library: nelexa/zip.
     *
     * @return ZipOutputFile
     */
    protected function getZipArchiveService()
    {
        /** @var ZipOutputFile $zip */
        $zip = $this->c['service.nelexa.zipoutput'];

        return $zip;
    }
}
