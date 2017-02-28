<?php

namespace Socialgest\Instapago;

use GuzzleHttp\Client;

class Instapago
{
    const API_HOST = 'https://api.instapago.com/';
    const TIMEOUT = 20.0;

    protected $api_url;
    protected $key_id;
    protected $public_key_id;
    protected $client;

    public function __construct()
    {
        $this->key_id = config('instapago.key_id');
        $this->public_key_id = config('instapago.public_key_id');
        $this->client = new Client([
            'base_uri' => self::API_HOST,
            'timeout'  => self::TIMEOUT,
        ]);
    }

    /**
     * Crear un pago diferido o reservado.
     *
     * @param array<string> $fields Los campos necesarios para procesar el pago.
     *
     * @return array<string> Respuesta de Instapago
     */
    public function reservePayment($fields)
    {
        return $this->createPayment('1', $fields);
    }

    /**
     * Crear un pago directo.
     *
     * @param array<string> $fields Los campos necesarios
     *                              para procesar el pago.
     *
     * @throws Exceptions\InstapagoException
     *
     * @return array<string> Respuesta de Instapago
     */
    public function directPayment($fields)
    {
        return $this->createPayment('2', $fields);
    }

    /**
     * Crear un pago.
     *
     * @param string        $type   tipo de pago ('1' o '2')
     * @param array<string> $fields Los campos necesarios
     *                              para procesar el pago.
     *
     * @throws Exceptions\InstapagoException
     *
     * @return array<string> Respuesta de Instapago
     */
    public function createPayment($type, $fields)
    {
        (new MyValidator())->payment()->checkThis($fields);

        $fields['KeyID'] = $this->key_id;

        $fields['PublicKeyId'] = $this->public_key_id;

        $fields['statusId'] = $type;

        $response = $this->createTransaccion('payment', $fields, 'POST');

        $result = $this->checkResponseCode($response);

        return $result;
    }

    /**
     * Completar Pago
     * Este método funciona para procesar un bloqueo o pre-autorización
     * para así procesarla y hacer el cobro respectivo.
     *
     * @param array<string> $fields Los campos necesarios
     *                              para procesar el pago.
     *
     * @throws Exceptions\InstapagoException
     *
     * @return array<string> Respuesta de Instapago
     */
    public function continuePayment($fields)
    {
        (new MyValidator())->payment()->checkThis($fields);

        $fields['KeyID'] = $this->key_id;

        $fields['PublicKeyId'] = $this->public_key_id;

        $response = $this->createTransaccion('complete', $fields, 'POST');

        $result = $this->checkResponseCode($response);

        return $result;
    }

    /**
     * Información/Consulta de Pago
     * Este método funciona para procesar un bloqueo o pre-autorización
     * para así procesarla y hacer el cobro respectivo.
     *
     * @param string $id_pago ID del pago a consultar
     *
     * @throws Exceptions\InstapagoException
     *
     * @return array<string> Respuesta de Instapago
     */
    public function query($idPago)
    {
        (new Validator())->query()->checkThis([
            'id' => $idPago,
        ]);

        $fields['KeyID'] = $this->key_id;

        $fields['PublicKeyId'] = $this->public_key_id;

        $fields['id'] = $idPago;

        $response = $this->createTransaccion('payment', $fields, 'POST');

        $result = $this->checkResponseCode($response);

        return $result;
    }

    /**
     * Cancelar Pago
     * Este método funciona para cancelar un pago previamente procesado.
     *
     * @param string $id_pago ID del pago a cancelar
     *
     * @throws Exceptions\InstapagoException
     *
     * @return array<string> Respuesta de Instapago
     */
    public function cancel($idPago)
    {
        (new Validator())->query()->checkThis([
            'id' => $idPago,
        ]);

        $fields['KeyID'] = $this->key_id;

        $fields['PublicKeyId'] = $this->public_key_id;

        $fields['id'] = $idPago;

        $response = $this->createTransaccion('payment', $fields, 'DELETE');

        $result = $this->checkResponseCode($response);

        return $result;
    }

    /**
     * Realiza Transaccion
     * Efectúa y retorna una respuesta a un metodo de pago.
     *
     * @param $url string endpoint a consultar
     * @param $fields array datos para la consulta
     * @param $method string verbo http de la consulta
     *
     * @return array resultados de la transaccion
     */
    public function createTransaccion($url, $fields, $method)
    {
        $args = [];

        if (!in_array($method, ['GET', 'POST', 'DELETE'])) {
            throw new Exception('Not implemented yet', 1);
        }

        $key = ($method == 'GET') ? 'query' : 'form_params';

        $args[$key] = $fields;

        try {
            $request = $this->client->request($method, $url, $args);
            $body = $request->getBody()->getContents();
            $response = json_decode($body, true);

            return $response;
        } catch (\GuzzleHttp\Exception\ConnectException $e) {
            throw new Exceptions\TimeoutException('Cannot connect to api.instapago.com');
        }
    }

    /**
     * Verifica y retornar el resultado de la transaccion.
     *
     * @param $response datos de la consulta
     *
     * @return array datos de transaccion
     */
    public function checkResponseCode($response)
    {
        $code = $response['code'];
        $msg = $response['message'];

        if ($code == 400) {
            throw new Exceptions\InstapagoException($msg);
        }

        if ($code == 401) {
            throw new Exceptions\InstapagoException($msg);
        }

        if ($code == 403) {
            throw new Exceptions\InstapagoException($msg);
        }

        if ($code == 500) {
            throw new Exceptions\InstapagoException($msg);
        }

        if ($code == 503) {
            throw new Exceptions\InstapagoException($msg);
        }

        if ($code == 201) {
            return [
            'code'              => $code,
            'msg_banco'         => $response['message'],
            'voucher'           => html_entity_decode($response['voucher']),
            'id_pago'           => $response['id'],
            'reference'         => $response['reference'],
            ];
        }

        throw new \Exception('Not implemented yet');
    }
}
