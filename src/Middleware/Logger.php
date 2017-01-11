<?php
/**
 * Created by PhpStorm.
 * User: philip
 * Date: 8/2/16
 * Time: 8:57 AM
 */

namespace App\Middleware;


use App\Services\Exception;
use Psr\Log\LoggerInterface;

/**
 * @method LoggerInterface emergency($message, array $context = [])
 * @method LoggerInterface alert($message, array $context = [])
 * @method LoggerInterface critical($message, array $context = [])
 * @method LoggerInterface error($message, array $context = [])
 * @method LoggerInterface warning($message, array $context = [])
 * @method LoggerInterface notice($message, array $context = [])
 * @method LoggerInterface info($message, array $context = [])
 * @method LoggerInterface debug($message, array $context = [])
 * @method LoggerInterface audit($message, array $context = [])
 *
 * @package App\Middleware
 */
class Logger
{

    protected $writers;

    /**
     * Logger constructor.
     * @param array $writers
     */
    public function __construct(array $writers)
    {
        // Run a quick validation on writers
        $this->writers = array_map(function ($writer) {
            // writer validation
            if (!$writer instanceof LoggerInterface) {
                throw new Exception(sprintf('Writer "%s" must be of type LoggerInterface', get_class($writer)));
            }
            return $writer;
        }, $writers);
   }

    public function __call($level, $args)
    {

        $message = array_shift($args);
        $context = array_shift($args) ?: [];

        foreach ($this->writers as $writer) {
            // Compensates for use of ->audit() for handling audit trail
            if (method_exists($writer, $level)) {
                $writer->$level($message, $context);
            }
        }
    }
}