<?php
namespace App\Printful\Method;

use App\Printful\AbstractApiMethod;

/**
 * Class ShippingRate
 *
 * @property string $address
 * @property string $city
 * @property string $countryCode
 * @property string $stateCode
 * @property string $zip
 * @property string $items read only
 *
 * @author Viktor Buzyka <vbuzyka@gmail.com>
 * @package App\Printful\Method
 */
class ShippingRate extends AbstractApiMethod
{
    /**
     * Request properties
     * @var array
     */
    private $params=[
        'address' => null,
        'city' => null,
        'countryCode' => null,
        'stateCode' => null,
        'zip' => null,
        'items' => []
    ];

    /**
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {
        $this->setParam($name, $value);
    }

    /**
     * Parameters setter
     * @todo Parameter validation could be added here in future
     * @param $name
     * @param $value
     */
    public function setParam($name, $value)
    {
        if (array_key_exists($name, $this->params)){
            $this->params[$name] = $value;
        }
    }

    /**
     * @param $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->getParam($name);
    }

    /**
     * Parameter getter
     * @param $name
     * @return mixed
     */
    public function getParam($name)
    {
        if (array_key_exists($name, $this->params)){
            return $this->params[$name];
        }
    }

    /**
     * @inheritDoc
     */
    public function requestPath(): string
    {
        return '/shipping/rates';
    }

    /**
     * @inheritDoc
     */
    public function requestMethod(): string
    {
        return 'POST';
    }

    /**
     * @inheritDoc
     */
    public function requestOptions(): array
    {
        return [
            'body' => $this->getRequestBody()
        ];
    }

    /**
     * Generate Json API request body
     * @return false|string
     */
    private function getRequestBody()
    {
        $body = [
            'recipient' => [
                'address1' => $this->address,
                'city' => $this->city,
                'country_code' => $this->countryCode,
                'state_code' => $this->stateCode,
                'zip' => $this->zip
            ],
            'items' => $this->getItems()
        ];
        return json_encode($body);
    }

    /**
     * @inheritDoc
     */
    protected function generateRequestCacheKey(): string
    {
        $keyArray = [
            $this->address,
            $this->city,
            $this->countryCode,
            $this->stateCode,
            $this->zip
        ];
        foreach ($this->getItems() as $item){
            $keyArray[] = $item['variant_id'] . '_' . $item['quantity'];
        }
        return implode('_', $keyArray);
    }

    /**
     * @param int $variantId
     * @param int $quantity
     */
    public function addItem (int $variantId, int $quantity )
    {
        if (!isset($this->params['items'])){
            $this->params['items']= [];
        }
        $this->params['items'][] = [
            'variant_id' =>  $variantId,
            'quantity' => $quantity
        ];
    }

    /**
     * @return mixed
     */
    public function getItems()
    {
        return $this->params['items'];
    }

    /**
     * Get API results
     * @return mixed
     * @throws \App\Printful\ApiRequestException
     */
    public function calculate()
    {
        return $this->runRequest();
    }

}