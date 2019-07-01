<?php
namespace App\Printful;

/**
 * Class ApiRequestException
 * Incapsulate error response from API
 *
 * @author Viktor Buzyka <vbuzyka@gmail.com>
 * @package App\Printful
 */
class ApiRequestException extends \Exception
{
    public function getName()
    {
        return 'Return api request error details';
    }

}