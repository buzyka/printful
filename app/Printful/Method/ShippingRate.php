<?php


namespace App\Printful\Method;

use App\Printful\AbstractApiMethod;

class ShippingRate extends AbstractApiMethod
{


    public $address;
    public $city;
    public $countryCode;
    public $stateCode;
    public $zip;

    private $items = [];


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


    public function addItem (int $variantId, int $quantity )
    {
        $this->items[] = [
            'variant_id' =>  $variantId,
            'quantity' => $quantity
        ];
    }

    public function getItems()
    {
        return $this->items;
    }

    public function calculate()
    {
        return $this->runRequest();
    }

}