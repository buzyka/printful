<?php


namespace LocalTest\Printful;

use App\Printful\ApiRequestException;
use PHPUnit\Framework\TestCase;
use App\Printful\ApiClient;
use App\Printful\Method\ShippingRate;
use App\Cache\AppCache;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;


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

    protected static $cacheConfigurationFixture;

    /**
     * @inheritDoc
     */
    public static function setUpBeforeClass(): void
    {
        self::$cacheConfigurationFixture = [
            'storage' => self::getTestsPath() . '/tmp'
        ];
    }

    /**
     * Get path to the tests folder
     * @return string
     */
    private static function getTestsPath()
    {
        $currentDir = dirname(__FILE__);
        while(basename($currentDir) != 'tests'){
            $currentDir = dirname($currentDir);
        }
        return $currentDir;
    }

    /**
     * @inheritDoc
     */
    public static function tearDownAfterClass(): void
    {
        $tmpPath = self::$cacheConfigurationFixture['storage'];
        foreach (glob($tmpPath . '/*') as $file) {
            unlink($file);
        }
    }

    /**
     * Mock the HTTP layer in Guzzle
     * @param int $status
     * @param $body
     */
    private function setGuzzleMock(int $status, $body)
    {
        $queue = [
            new Response($status, [], $body)
        ];
        $mock = new MockHandler($queue);
        $handler = HandlerStack::create($mock);
        ApiClient::setGuzzleMock($handler);
    }

    private function getShippingRate()
    {
        // You don't need put real api-key here. All api requests should be mocked.
        $apiKey = 'Some_Key';
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



        $expectedJson = '[
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
    ]';

        $this->setGuzzleMock(200, $this->correctResponseFixture);
        $response = $shippingRate->calculate();
        $this->assertIsArray($response);
        $this->assertEquals(json_decode($expectedJson), $response);
    }

    public function testCalculateException()
    {
        $shippingRate = $this->getShippingRate();
        $shippingRate->address = '11025 Westlake Dr';
        $shippingRate->city = 'Charlotte';
        $shippingRate->stateCode = 'NC';
        $shippingRate->zip = 28273;
        $shippingRate->addItem(7679, 2);

        $mockBody = '{
    "code": 400,
    "result": "Missing recipient country code",
    "error": {
        "reason": "BadRequest",
        "message": "Missing recipient country code"
    }
}';
        $this->setGuzzleMock(400, $mockBody);

        $this->expectException(ApiRequestException::class);
        $this->expectExceptionMessage('Missing recipient country code');
        $this->expectExceptionCode(400);
        $shippingRate->calculate();
    }

    public function testCache()
    {
        $shippingRate =  $this->getMockShippingRate();
        $shippingRate->setCacheDuration(5*60);
        $shippingRate->address = '11025 Westlake Dr';
        $shippingRate->city = 'Charlotte';
        $shippingRate->stateCode = 'NC';
        $shippingRate->countryCode = 'US';
        $shippingRate->zip = 28273;
        $shippingRate->addItem(7679, 2);

        $this->setGuzzleMock(200, $this->correctResponseFixture);

        $shippingRate->calculate();
        $this->assertEquals(1, $shippingRate->mockRequestCounter);
        $this->setGuzzleMock(200, $this->correctResponseFixture);
        $shippingRate->calculate();
        $this->assertEquals(1, $shippingRate->mockRequestCounter);
    }

    private function getMockShippingRate()
    {
        // You don't need put real api-key here. All api requests should be mocked.
        $apiKey = 'Some_Key';
        AppCache::init('File', self::$cacheConfigurationFixture);

        $srObject = new class($apiKey, AppCache::getInstance()) extends \App\Printful\Method\ShippingRate {
            public $mockRequestCounter=0;

            protected function requestThroughApiClient()
            {
                $this->mockRequestCounter++;
                return parent::requestThroughApiClient();
            }
        };

        return $srObject;
    }

}