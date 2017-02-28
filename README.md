![Php Instapago](asset/logo.png)

<p align="center">
    Library for Instapago in Laravel 5.* (Version 1.0.0)
</p>

<p align="center">
    <sup style="color: #d0d0d0;"><b>Note</b> The logos are owned by Instapago and Banesco, respectively..</sup>
</p>

[![GitHub issues](https://img.shields.io/github/issues/socialgest/laravel-instapago.svg?style=flat-square)](https://github.com/socialgest/laravel-instapago/issues) [![GitHub forks](https://img.shields.io/github/forks/socialgest/laravel-instapago.svg?style=flat-square)](https://github.com/socialgest/laravel-instapago/network) [![GitHub license](https://img.shields.io/badge/license-MIT-blue.svg?style=flat-square)](https://raw.githubusercontent.com/abr4xas/php-instapago/master/LICENSE)

## Installation

To install, run the following command in your project directory

```bash
$ composer require socialgest/laravel-instapago

```

Then in `config/app.php` add the following to the `providers` array:

```php
Socialgest\Instapago\InstapagoServiceProvider::class
```


Also, if you must (recommend you don't), add the Facade class to the `aliases` array in `config/app.php` as well:

```php
'Instapago'    => Socialgest\Instapago\Facades\Instapago::class
```

**But it'd be best to just inject the class, like so (this should be familiar):**

```php
use Socialgest\Instapago\Instapago;
```

## Configuration

### Set in .env

```
INSTAPAGO_KEY_ID = 74D4A278-C3F8-4D7A-9894-FA0571D7E023
INSTAPAGO_PUBLIC_KEY_ID = e9a5893e047b645fed12c82db877e05a

```


## Example Usage

``` php

use Socialgest\Instapago\Instapago;

...

public function pay()
{
    $paymentData = [
        'amount' => '200',
        'description' => 'test',
        'cardHolder' => 'jon doe',
        'cardHolderId' => '11111111',
        'cardNumber' => '4111111111111111',
        'cvc' => '123',
        'expirationDate' => '12/2019',
        'IP' => '127.0.0.1',
    ];

    try{
            $instapago = new Instapago();
            $respuesta = $instapago->directPayment($paymentData);
            // hacer algo con la respuesta
    } catch(\Socialgest\Instapago\Instapago\Exceptions\InstapagoException $e){
        // manejar el error
    } catch(\Socialgest\Instapago\Instapago\Exceptions\TimeoutException $e){
        // manejar el error
    }     
}

```
## Documentation

[Documentation (EN)](https://instapago.com/wp-content/uploads/Integration-Guide-Instapago-API-1.6.pdf)

[Documentación (ES)](https://instapago.com/wp-content/uploads/2016/02/Guia-Integracion-API-Instapago-1.6.pdf)

### Key for test

```
* keyId = 74D4A278-C3F8-4D7A-9894-FA0571D7E023
* publicKeyId = e9a5893e047b645fed12c82db877e05a
```

License [MIT](http://opensource.org/licenses/MIT) :copyright: 2016