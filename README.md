# Prontful

INSTALLATION
------------

Repository should be cloned to the local machine with already installed [composer](https://getcomposer.org/doc/00-intro.md).

Then, go to the working directory run composer install command
```bash
composer install
```

COMPONENTS
------------

app\Cache - implementation of caching mechanism
app\Printful - implementation of printful API-client
app\Printful\Method\ShippingRate - implementation Shipping Rate method

test - php unit tests

HOW TO USE?
------------
Example using implemented Shipping rate with cache mechanism:

```php
require_once __DIR__ . '/vendor/autoload.php';

// Crate and set folder for cache storage
$cacheStorage = __DIR__ . '/tmp';
if (!is_dir($cacheStorage)){
    mkdir($cacheStorage);
}

// Declare API Key
$apiKey = '77qn9aax-qrrm-idki:lnh0-fm2nhmp0yca7';

// Initialize method instance and set cache
$shippingRate = new \App\Printful\Method\ShippingRate(
    $apiKey,
    \App\Cache\AppCache::init('File', ['storage' => $cacheStorage])
);

// Define request details
$shippingRate->address = '11025 Westlake Dr';
$shippingRate->city = 'Charlotte';
$shippingRate->countryCode = 'US';
$shippingRate->stateCode = 'NC';
$shippingRate->zip = '28273';
$shippingRate->addItem(7679, 2);

// Print response
print_r($shippingRate->calculate());
```

PHP UNIT TESTS
------------

Running all tests

```bash
php ./vendor/bin/phpunit tests
```

Running Shipping rate tests

```bash
php ./vendor/bin/phpunit tests/Printful/Method/ShippingRateTest.php
```