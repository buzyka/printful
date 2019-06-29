<?php


namespace LocalTest\Printful;

use PHPUnit\Framework\TestCase;
use App\Cache\AppCache;
use App\Printful\Method\ShippingRate;
use App\Printful\ApiClient;

class ApiClientTest extends TestCase
{

    private $correctResponseFixture = '{
    "code": 200,
    "result": [
        {
            "id": "PRINTFUL_MEDIUM",
            "name": "Standard (3-5 business days after fulfillment)",
            "rate": "4.29",
            "currency": "USD"
        },
        {
            "id": "STANDARD",
            "name": "Flat Rate (3-5 business days after fulfillment)",
            "rate": "5.75",
            "currency": "USD"
        },
        {
            "id": "PRINTFUL_FAST",
            "name": "Express (1-3 business days after fulfillment)",
            "rate": "6.68",
            "currency": "USD"
        }
    ],
    "extra": []
}';

    public function testRequest()
    {
        $apiKey = '77qn9aax-qrrm-idki:lnh0-fm2nhmp0yca7';
        AppCache::init('Dummy');
        $cacheInstance = AppCache::getInstance();
        $shippingRate = new ShippingRate($apiKey, $cacheInstance);
        $shippingRate->address = '11025 Westlake Dr';
        $shippingRate->city = 'Charlotte';
        $shippingRate->stateCode = 'NC';
        $shippingRate->countryCode = 'US';
        $shippingRate->zip = 28273;
        $shippingRate->addItem(7679, 2);

        $client = new ApiClient();
        $client->setApiKey($apiKey);
        $expectedObject = json_decode($this->correctResponseFixture);
        $this->assertEquals($expectedObject, $client->request($shippingRate));
    }

}