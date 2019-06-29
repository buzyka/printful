<?php


namespace LocalTest\Printful;

use App\Printful\ApiRequestException;
use PHPUnit\Framework\TestCase;
use App\Printful\Method\ShippingRate;
use App\Cache\AppCache;

class ShipingRateTest extends TestCase
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

    private function getShippingRate()
    {
        $apiKey = '77qn9aax-qrrm-idki:lnh0-fm2nhmp0yca7';
        AppCache::init('Dummy');
        $cacheInstance = AppCache::getInstance();
        return new ShippingRate($apiKey, $cacheInstance);
    }

    public function testRequestPath()
    {
        $shippingRate = $this->getShippingRate();
        $this->assertEquals('/shipping/rates', $shippingRate->requestPath());
    }

    public function testRequestMethod()
    {
        $shippingRate = $this->getShippingRate();
        $this->assertEquals('POST', $shippingRate->requestMethod());
    }

    public function testAddItem()
    {
        $shippingRate = $this->getShippingRate();
        $shippingRate->addItem(7679, 2);
        $expectedArray = [
            [
                'variant_id' =>  7679,
                'quantity' => 2
            ]
        ];
        $this->assertEquals($expectedArray, $shippingRate->getItems());
    }


    public function testSetAddress()
    {
        $shippingRate = $this->getShippingRate();
        $shippingRate->address = '11025 Westlake Dr';
        $shippingRate->city = 'Charlotte';
        $shippingRate->stateCode = 'NC';
        $shippingRate->countryCode = 'US';
        $shippingRate->zip = 28273;
        $shippingRate->addItem(7679, 2);

        $expectedOptions = [
           'body' => '{
    "recipient": {
        "address1": "11025 Westlake Dr",
        "city": "Charlotte",
        "country_code": "US",
        "state_code": "NC",
        "zip": 28273
    },
    "items": [
        {
            "quantity": 2,
            "variant_id": 7679
        }
    ]
}'
        ];
        $options = $shippingRate->requestOptions();
        $this->assertArrayHasKey('body', $options);
        $this->assertEquals(json_decode($expectedOptions['body']), json_decode($options['body']));
    }

    public function testCalculate()
    {
        $shippingRate = $this->getShippingRate();
        $shippingRate->address = '11025 Westlake Dr';
        $shippingRate->city = 'Charlotte';
        $shippingRate->stateCode = 'NC';
        $shippingRate->countryCode = 'US';
        $shippingRate->zip = 28273;
        $shippingRate->addItem(7679, 2);

        $expectedArray = json_decode('[
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
    ]');
        $response = $shippingRate->calculate();
        $this->assertIsArray($response);
        $this->assertEquals($expectedArray, $response);
    }

    public function testCalculateException()
    {
        $shippingRate = $this->getShippingRate();
        $shippingRate->address = '11025 Westlake Dr';
        $shippingRate->city = 'Charlotte';
        $shippingRate->stateCode = 'NC';
        $shippingRate->zip = 28273;
        $shippingRate->addItem(7679, 2);

        $this->expectException(ApiRequestException::class);
        $this->expectExceptionMessage('Missing recipient country code');
        $this->expectExceptionCode(400);
        $shippingRate->calculate();
    }

}