![Php Instapago](asset/logo.png)
<p align="center">
Documentación de la librería <b>Instapago</b>
</p>

----

Table of Contents
=================
* [Información General](#información-general)
  * [Credenciales de Pruebas](#credenciales-de-pruebas)
  * [Parámetros <em>requeridos</em> para crear un pago](#parámetros-requeridos-para-crear-un-pago)
  * [Retornos](#retornos)
  * [Manejo de errores](#manejo-de-errores)
  * [Códigos de respuesta](#códigos-de-respuesta)
  * [Tarjetas de prueba](#tarjetas-de-prueba)
* [API](#api)
  * [Instanciación](#instanciación)
  * [Crear un Pago Directo](#crear-un-pago-directo)
  * [Reservar un Pago](#reservar-un-pago)
  * [Completar Pago](#completar-pago)
  * [Información de un Pago](#información-de-un-pago)
* [Licencia](#licencia)


## Información General
### Credenciales de Pruebas
```
* keyId = 74D4A278-C3F8-4D7A-9894-FA0571D7E023
* publicKeyId = e9a5893e047b645fed12c82db877e05a
```

> **Importante**: Debes solicitar las llaves públicas y privadas (`publicKeyId` y `keyId`) a Instapago. [Aquí](http://instapago.com/wp-content/uploads/2015/10/Guia-Integracion-API-Instapago-1.6.pdf) puedes encontrar mayor información.


### Parámetros _requeridos_ para crear un pago

* `cardHolder`: Nombre del Tarjeta habiente.
* `cardHolderId`: Cédula del Tarjeta Habiente, 
* `cardNumber`: Número de la tarjeta de crédito, 16 dígitos sin separadores.
* `cvc`: Código de validación de la Tarjeta de crédito.
* `expirationDate`: Fecha de Vencimiento de la tarjeta. Formato MM/YYYY. Por Ejemplo: 10/2015.
* `amount`: Monto a Debitar, formato: `0.00` (punto como separador decimal, sin separador de miles).
* `description`: Texto con la descripción de la operación.
* `IP`: Dirección IP del cliente.

De ahora en más usaremos `$paymentData` para referirnos a el arreglo con los parámetros antes mencionados.

```php
$paymentData = [
  'amount'         => '200',
  'description'    => 'test',
  'cardHolder'     => 'jon doe',
  'cardHolderId'   => '11111111',
  'cardNumber'     => '4111111111111111',
  'cvc'            => '123',
  'expirationDate' => '12/2019',
  'IP'             => '127.0.0.1',
];
```

### Retornos

Todos los métodos del api devuelven un arreglo asociativo con el siguiente esquema:

* `code`: (numeric) Codigo de respuesta de Instapago.
* `msg_banco`: (string) Mensaje del banco respecto a la transacción.
* `voucher`: (string) Voucher (muy parecido al ticket que emite el POS en Venezuela) en html.
* `id_pago`: (string) Identificador del pago en la plataforma de Instapago.
* `reference`: (numeric) Referencia del pago en la red bancaria.

### Manejo de errores

La excepción base de la librería es `\Socialgest\Instapago\Instapago\Exceptions\InstapagoException` y reporta errores generales con instapago, y de ella se derivan 2 excepciones de la siguiente manera.

* `\Socialgest\Instapago\Instapago\Exceptions\TimeoutException`: es lanzada cuando es imposible conectarse al api de Instapago y expira el tiempo de carga.

### Códigos de respuesta

Para todas las transacciones realizadas bajo el API de Instapago, los códigos HTTP de respuestas corresponden a los siguientes estados:

* ```201```: Pago procesado con éxito.
* ```400```: Error al validar los datos enviados (Adicionalmente se devuelve una cadena de caracteres con la descripción del error).
* ```401```: Error de autenticación, ha ocurrido un error con las llaves utilizadas.
* ```403```: Pago Rechazado por el banco.
* ```500```: Ha Ocurrido un error interno dentro del servidor.
* ```503```: Ha Ocurrido un error al procesar los parámetros de entrada. Revise los datos enviados y vuelva a intentarlo.

> **Importante**: Si recibe un código de respuesta diferente a los antes descritos deben ser tomados como errores de protocolo HTTP.

### Tarjetas de prueba

Para realizar las pruebas, se provee de los siguientes datos para comprobar la integración:

* Tarjetas Aprobadas:

Pueden indicar cualquier valor para Cédula o RIF, Fecha de Vencimiento y CVC:

* Visa: ```4111111111111111```
* American Express: ```378282246310005```
* MasterCard: ```5105105105105100```
* Sambil: ```8244001100110011``
* Rattan: ```8244021100110011```
* Locatel: ```8244041100110011```


## API

### Instanciación

En el arreglo `providers` del archivo `config/app.php` agregar:

```php
Socialgest\Instapago\InstapagoServiceProvider::class
```

Además, si debes (te recomendamos que no), agrega la clase Facade al array `aliases` en` config/app.php` así:

```php
'Instapago'    => Socialgest\Instapago\Facades\Instapago::class
```

** Pero sería mejor inyectar la clase, así (esto debería ser familiar): **

```php
use Socialgest\Instapago\Instapago;
```

### Set in .env

```
INSTAPAGO_KEY_ID = 74D4A278-C3F8-4D7A-9894-FA0571D7E023
INSTAPAGO_PUBLIC_KEY_ID = e9a5893e047b645fed12c82db877e05a

```

### Crear un Pago Directo

Efectúa un pago directo con tarjeta de crédito, los pagos directos son inmediatamente debitados del cliente y entran en el proceso bancario necesario para acreditar al beneficiario.

```php

use Socialgest\Instapago\Instapago;

try{

  $api = new Instapago();

  $respuesta = $api->directPayment($paymentData);
  // hacer algo con $respuesta
}catch(\Instapago\Exceptions\InstapagoException $e){

  echo "Ocurrió un problema procesando el pago.";
  // manejar el error 
} catch(\Socialgest\Instapago\Instapago\Exceptions\TimeoutException $e){

  echo "Ocurrió un problema procesando el pago.";
  // manejar el error 
} 
```

### Reservar un Pago

Efectúa una reserva o retención de pago en la tarjeta de crédito del cliente, la reserva diferirá los fondos por un tiempo (3 días máximo segun fuentes extraoficiales), en el plazo en el que los fondos se encuentren diferidos, ni el beneficiario ni el cliente poseen el dinero. El dinero será tramitado al beneficiario una vez completado el pago, o de lo contrario será acreditado al cliente de vuelta si no se completa durante el plazo o si se cancela el pago.

```php

use Socialgest\Instapago\Instapago;

try{

  $api = new Instapago();

  $respuesta = $api->reservePayment($paymentData);
  // hacer algo con $respuesta
}catch(\Instapago\Exceptions\InstapagoException $e){

  echo "Ocurrió un problema procesando el pago.";
  // manejar el error 
} catch(\Socialgest\Instapago\Instapago\Exceptions\TimeoutException $e){

  echo "Ocurrió un problema procesando el pago.";
  // manejar el error 
} 
```

### Completar Pago

Éste método permite cobrar fondos previamente retenidos. 

* `id`: Identificador único del pago.
* `amount`: Monto por el cual se desea procesar el pago final.

```php

use Socialgest\Instapago\Instapago;

try{

  $api = new Instapago();

  $respuesta = $api-continuePayment([
    'id' => 'af614bca-0e2b-4232-bc8c-dbedbdf73b48',
    'amount' => '200.00'
  ]);

}catch(\Instapago\Exceptions\InstapagoException $e){
  // manejar errores
} catch(\Socialgest\Instapago\Instapago\Exceptions\TimeoutException $e){

  echo "Ocurrió un problema procesando el pago.";
  // manejar el error 
} 
```

### Información de un Pago

Consulta información sobre un pago previamente generado.

```php

use Socialgest\Instapago\Instapago;

try{

  $api = new Instapago();

  $idPago = 'af614bca-0e2b-4232-bc8c-dbedbdf73b48';
  
  $respuesta = $api->query($idPago);

}catch(\Instapago\Exceptions\InstapagoException $e){
  // manejar errores
} catch(\Socialgest\Instapago\Instapago\Exceptions\TimeoutException $e){

  echo "Ocurrió un problema procesando el pago.";
  // manejar el error 
} 
```
Devuelve la misma respuesta que los métodos de crear pagos.

### Anular Pago

Este método permite cancelar un pago, haya sido directo o retenido.

```php

use Socialgest\Instapago\Instapago;

try{

  $api = new Instapago();

  $idPago = 'af614bca-0e2b-4232-bc8c-dbedbdf73b48';

  $info = $api->cancel($idPago);
  
}catch(\Instapago\Exceptions\InstapagoException $e){
  // manejar errores
} catch(\Socialgest\Instapago\Instapago\Exceptions\TimeoutException $e){

  echo "Ocurrió un problema procesando el pago.";
  // manejar el error 
} 
```
Devuelve la misma respuesta que los métodos de crear pagos.


## Licencia

Licencia [MIT](http://opensource.org/licenses/MIT) :copyright: 2015
