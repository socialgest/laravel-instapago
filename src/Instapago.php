<?php

namespace Socialgest\Instapago;

use GuzzleHttp\Client;
use Validator;
use Exception;

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
     * Friendly welcome
     *
     * @param string $phrase Phrase to return
     *
     * @return string Returns the phrase passed in
     */
    public function createPayment($fields)
    {
        $fields['KeyID'] = $this->key_id;
        $fields['PublicKeyId'] = $this->public_key_id;
        $rules = array(
            'KeyID' => 'required',
            'PublicKeyId' => 'required',
            'Amount' => 'required|string',
            'Description' => 'required|string|min:1|max:50',
            'CardHolder' => 'required|regex:/^[A-Z üÜáéíóúÁÉÍÓÚñÑ]{1,50}$/i',
            'CardHolderId' =>  'required|numeric|digits_between:6,8',
            'CardNumber' => 'required|numeric|digits_between:15,16',
            'CVC' => 'required|numeric|digits:3',
            'ExpirationDate' => 'required|date_format:m/Y|after:tomorrow',
            'StatusId' => 'required|integer|min:1|max:2',
            'IP' => 'required|ip',
        );
        $validator = Validator::make($fields, $rules);
        if($validator->fails()) 
        {
            $return['code'] = 399;
            $return['error'] = $validator->errors()->all()[0];
            $return = json_decode(json_encode($return));
            return $return;
        }
        $response = $this->client->request('POST', 'payment', [
            'form_params' => $fields
        ]);
        return json_decode($response->getBody());
    }
}
