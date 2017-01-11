<?php
/**
 * Created by PhpStorm.
 * User: philip
 * Date: 9/7/16
 * Time: 4:56 PM
 */

namespace App\MailOrder\Model;

/**
 * Class ParseError
 * @package App\Model
 *
 * @property string ObjectLineNumber
 * @property string Error
 * @property string Message
 * @property string Filename
 */
class ParseError extends AbstractModel
{
    protected function getFieldSequence()
    {
        return array(
            'ObjectLineNumber',
            'Error',
            'Message',
            'Filename',
        );
    }
}