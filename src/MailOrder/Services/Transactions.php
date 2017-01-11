<?php
/**
 * Created by PhpStorm.
 * User: philip
 * Date: 7/28/16
 * Time: 1:34 PM
 */

namespace App\MailOrder\Services;


use App\MailOrder\Model\Fulfillment;
use App\MailOrder\Model\FulfillmentLog;
use App\MailOrder\Model\FulfillmentResult;
use App\MailOrder\Model\ParseError;
use App\Services\AbstractService;
use App\Services\Request;
use GuzzleHttp\Exception\RequestException;
use League\Csv\Reader;
use App\Services\FlySystemTrait;

class Transactions extends AbstractService
{
    use FlySystemTrait;

    public function checkForNew()
    {
        /** @var Request $request */
        $request = $this->c['service.request'];
        $logger = $this->c['service.logger'];

        $logger->info('Checking for new transactions');

        $uri = $this->getClientConfig()['apis']['api'];
        $endpoint = $this->getConfig()['endpoints']['check_for_transactions'];

        $params = array(
            'status'      => 'OPEN',
            'refill_date' => '|' . date('Y-m-d'),
        );

        try {
            $logger->debug('Calling Endpoint: ' . $uri . $endpoint);
            $response = $request->get($uri . $endpoint . '?' . http_build_query($params), [
                'authenticate' => true,
                ''             => '',
            ]);
        } catch (RequestException $e) {
            if ($e->getResponse() /* For failed connection checks */ && $e->getResponse()->getStatusCode() == 404) {
                return false;
            } else {
                throw $e;
            }
        }

        $fills = \GuzzleHttp\json_decode((string)$response->getBody());

        $fill_ids = [];
        foreach ($fills as $f) {
            if (isset($f->id)) {
                $fill_ids[] = $f->id;
            }
        }

        return $fill_ids;
    }

    public function getFileForFills(array $fill_ids)
    {
        /** @var Request $request */
        $request = $this->c['service.request'];

        $uri = $this->getClientConfig()['apis']['api'];
        $endpoint = $this->getConfig()['endpoints']['generate_file_for'];

        $response = $request->post($uri . $endpoint, [
            'authenticate' => true,
            'json'         => ['prescription_fill_ids' => $fill_ids],
        ]);

        if ($response->getStatusCode() != 201) {
            throw new Exception(sprintf('API Endpoint %s returned %d expected 201: %s', ($uri . $endpoint),
                $response->getStatusCode(), (string)$response->getBody()));
        }

        return \GuzzleHttp\json_decode((string)$response->getBody())->filename;
    }

    public function settle(array $fill_ids)
    {
        /** @var Request $request */
        $request = $this->c['service.request'];

        $uri = $this->getClientConfig()['apis']['api'];
        $endpoint = $this->getConfig()['endpoints']['settle_transaction'];

        $successful_ids = [];
        foreach ($fill_ids as $id) {
            $this->getLogger()->debug(sprintf('Settling fill id: %d', $id));

            try {
                $new_endpoint = str_replace(':fill_id', $id, $endpoint);
                $response = $request->post($uri . $new_endpoint, [
                    'authenticate' => true,
                ]);
            } catch (RequestException $e) {
                if ($e->getResponse() /* For failed connection checks */ && $e->getResponse()->getStatusCode() == 404) {
                    continue; // These errors are ignorable by the CLI
                }

                $this->getLogger()->alert($e); // Alert on it, but don't stop the script
                continue;
            }

            if ($response->getStatusCode() != 201) {
                $this->getLogger()->alert(sprintf('API Endpoint %s returned %d expected 201: %s',
                    ($uri . $new_endpoint), $response->getStatusCode(), (string)$response->getBody()));
            }

            $result = \GuzzleHttp\json_decode((string)$response->getBody());

            $this->getLogger()->debug(sprintf('Successfully settled fill id: %d', $result->prescription_fill_id));
            $successful_ids[] = $result->prescription_fill_id;
        }

        return $successful_ids;
    }

    /**
     * @param $filename
     * @return array
     * @throws Exception
     */
    public function parseTransactionFile($filename)
    {
        $this->getLogger()->debug(sprintf('Identified %s as %s file', $filename, FulfillmentResult::EXTENSION));

        $dirs = $this->getClientConfig()['dirs'];

        $incoming_dir = join(DIRECTORY_SEPARATOR, [$dirs['base_dir'], $dirs['incoming_dir']]);
        $file_path = realpath(join(DIRECTORY_SEPARATOR, [$incoming_dir, $filename]));

        return $this->_parse($file_path, 'App\MailOrder\Model\FulfillmentResult');
    }

    public function parseLogFile($filename)
    {
        $this->getLogger()->debug(sprintf('Identified %s as %s file', $filename, FulfillmentLog::EXTENSION));

        $dirs = $this->getClientConfig()['dirs'];
        $incoming_dir = join(DIRECTORY_SEPARATOR, [$dirs['base_dir'], $dirs['incoming_dir']]);
        $file_path = realpath(join(DIRECTORY_SEPARATOR, [$incoming_dir, $filename]));

        $orf_file = join('.', [pathinfo($filename, PATHINFO_FILENAME), Fulfillment::EXTENSION]);
        $fills = $this->readOrf($orf_file);

        $failures = $this->identifyErrors($file_path); // What IF log files parsing?

        $this->getLogger()->debug(sprintf('Identified %d lines in error.', count($failures)));

        $errorFills = $this->identifyFills($fills, $failures);

        $this->getLogger()->debug(sprintf('Error fill IDs: %s', join(', ', array_map(function ($k) {
            return $k->OrderNumber;
        }, $errorFills))));

        $this->setAsError($errorFills, $failures);

        return true;
    }

    /**
     * @param FulfillmentResult[] $fulfillments Array of FulfillmentResult objects
     * @return array
     */
    public function update(array $fulfillments)
    {
        $transaction_log = [];

        foreach ($fulfillments as $row => $fulfillment) {
            try {

                if ($fulfillment instanceof ParseError) {
                    throw new Exception(sprintf('Failure in file parsing: %s', $fulfillment->Message));
                }

                $fulfillment->validate();
                $this->putFill($fulfillment->OrderNumber, [
                    'tracking_number'   => $fulfillment->TrackingNumber,
                    'response_filename' => $fulfillment->Filename,
                    'status'            => 'SHIPPED',
                    'exception_message' => null, // In the event there is an old message
                ]);
            } catch (\Exception $e) {

                $transaction_log[] = [
                    $row,
                    FulfillmentLog::STATUS_ERROR,
                    $e->getMessage(),
                ];

                $this->getLogger()->critical($e->getMessage());
                continue;
            }

            $transaction_log[] = [
                $row,
                FulfillmentLog::STATUS_SUCCESS,
                "",
            ];
        }

        return $transaction_log;
    }

    public function exportLog($file, array $log)
    {
        $logfile = join('.', [pathinfo($file, PATHINFO_FILENAME), FulfillmentLog::EXTENSION]);
        $dirs = $this->getClientConfig()['dirs'];

        $path = join(DIRECTORY_SEPARATOR,
            [$dirs['base_dir'], $dirs['export_dir'], $logfile]);

        $this->getLogger()->info(sprintf('Generating %s into %s.', $logfile, $dirs['export_dir']));

        return $this->createCsvWriter($path)->insertAll($log);
    }

    /**
     * @param $full_path string  CSV File path
     * @param $object    string  Models object to use for breaking out
     * @return array  Array object passed in
     * @throws Exception
     */
    protected function _parse($full_path, $object)
    {
        if (!file_exists($full_path)) {
            throw new Exception(sprintf('File %s does not exist', $full_path));
        }

        $this->getLogger()->info(sprintf('Parsing %s as %s', pathinfo($full_path, PATHINFO_BASENAME), $object));

        /** @var Reader $csv */
        $csv_class = $this->c['service.csv.reader'];
        $csv = $csv_class::createFromPath($full_path);

        $fills = [];
        $results = $csv->fetch();
        foreach ($results as $idx => $r) {
            $rnum = $idx + 1;  // We're 1 indexing the line # intentionally to match .LOG "ORF LineNo", else it's irrelevant
            try {
                $fills[$rnum] = new $object($r);
                $fills[$rnum]->Filename = pathinfo($full_path, PATHINFO_FILENAME);
            } catch (\Exception $e) {
                $message = sprintf('Error parsing %s Row %d: %s', pathinfo($full_path, PATHINFO_BASENAME), $idx,
                    $e->getMessage());

                $fills[$rnum] = new ParseError([
                    $rnum,
                    true,
                    $message,
                    pathinfo($full_path, PATHINFO_FILENAME),
                ]);

                $this->getLogger()->error($message);
            }
        }

        return $fills;
    }

    protected function identifyErrors($full_path)
    {
        $log = $this->_parse($full_path, 'App\MailOrder\Model\FulfillmentLog');
        $failed = [];

        /** @var FulfillmentLog $line */
        foreach ($log as $line) {
            // Lines that fail to parse will be ParseError objects; we don't care about these when working with .LOG
            //    In reality: ParseError is a BAD response from MailOrder, and we have no recourse: manual watch of critical raised when ParseError is created
            if ($line instanceof FulfillmentLog && $line->Status == FulfillmentLog::STATUS_ERROR) {
                $failed[$line->OFSLineNumber] = $line;
            }
        }

        return $failed;
    }

    /**
     * @param Fulfillment[]           $fills
     * @param string|FulfillmentLog[] $errors
     */
    protected function setAsError($fills, $errors)
    {
        foreach ($fills as $idx => $f) {
            $this->putFill($f->OrderNumber, [
                'status'            => FulfillmentLog::STATUS_EXCEPTION,
                'exception_message' => is_scalar($errors) ? $errors : $errors[$idx]->Reason,
            ]);
        }
    }

    protected function identifyFills(&$fills, &$lines)
    {
        return array_intersect_key($fills, $lines);
    }

    protected function moveOverORF($file)
    {
        $this->getLogger()->info(sprintf('Moving "%s" to incoming dir.', $file));
        $dirs = $this->getClientConfig()['dirs'];

        return $this->getFileSystemService()->rename(join(DIRECTORY_SEPARATOR, [$dirs['processing_dir'], $file]),
            join(DIRECTORY_SEPARATOR, [$dirs['incoming_dir'], $file]));
    }

    /**
     * Sends Fill to API
     *
     * @param $proscription_fill_id
     * @param $args
     * @return bool
     */
    protected function putFill($proscription_fill_id, $args)
    {
        /** @var Request $request */
        $request = $this->c['service.request'];

        $uri = $this->getClientConfig()['apis']['api'];
        $endpoint = str_replace(':fill_id', $proscription_fill_id,
            $this->getConfig()['endpoints']['update_transaction']);

        $args['json'] = $args;
        $args['authenticate'] = true; // Always true for PUT

        $response = $request->put($uri . $endpoint, $args);

        if ($response->getStatusCode() != 204) {
            $this->getLogger()->critical(sprintf('API Endpoint "%s" returned "%d" expected 204: %s', ($uri . $endpoint),
                $response->getStatusCode(), (string)$response->getBody()));
        }

        return true;
    }

    /**
     * @param $orf_file
     * @return array
     */
    protected function readOrf($orf_file)
    {
        $this->getLogger()->info(sprintf('Reading in %s.', $orf_file));
        $dirs = $this->getClientConfig()['dirs'];

        return $this->_parse(realpath(join(DIRECTORY_SEPARATOR,
            [$dirs['base_dir'], $dirs['export_dir'], $dirs['completed_dir'], $orf_file])),
            'App\MailOrder\Model\Fulfillment');
    }
}