<?php


namespace App\Printful;


class ApiRequestException extends \Exception
{
    public function getName()
    {
        return 'Return api request error details';
    }

}