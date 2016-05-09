<?php
/**
 * Created by PhpStorm.
 * User: rmorenp
 * Date: 23-04-16
 * Time: 4:30 PM
 */

namespace PuntoPagos;


use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

class PuntoPagos
{
    private $url = 'https://www.puntopagos.com';
    private $key = null;
    private $secret = null;

    /**
     * PuntoPagos constructor.
     * @param string $key Llave publica de la integración
     * @param string $secret Llave privada de la integración
     * @param array $config Configuraciones adicionales
     */
    public function __construct($key, $secret, Array $config = [])
    {
        $this->key = $key;
        $this->secret = $secret;
        if(isset($config['env'])){
            $env=strtolower($config['env']);
            switch($env){
                case 'sandbox':
                    $this->url = 'https://sandbox.puntopagos.com';
                    break;
                case 'dev':
                    $this->url = 'https://sandbox.puntopagos.com';
                    break;
                default:
                    $this->url = 'https://www.puntopagos.com';
            }
        }
    }

    /**
     * @param string $clientId Identificador asignado por la plataforma cliente
     * @param float $mount Monto a cobrar
     * @param array $options Opciones de personalizacion por el momento solo aguanta gateway_type para medio de pago
     * @return Transaction Entrega un objecto con el contenido de la transacción
     */
    public function createTransaction($clientId,$mount,Array $options = []){

        $mount = number_format($mount, 2, '.', '');
        $client = new Client([
            'base_uri' => $this->url,
        ]);
        $message = [
            'trx_id'=> $clientId,
            'monto' => $mount,
        ];
        if(isset($options['gateway_type'])&&is_int($options['gateway_type'])){
            $message['medio_pago'] = $options['gateway_type'];
        }
        $datetime = (new \DateTime())->format("D, d M Y H:i:s")." GMT";

        $parametersBySign = [
            'transaccion/crear',
            $clientId,
            $mount,
            $datetime
        ];

        try{
            /* TODO: reemplazar el GMT por la validacion del formato Datetime::RFC1123
                     ya que servidor de punto pago no aplica correctamente validacion de tiempo con otro timezone
            */
            $response = $client->request('POST','transaccion/crear',[
                'json' => $message,
                'headers' => [
                    'Accept'=> 'application/json',
                    "Content-Type" => "application/json; charset=utf-8",
                    'Accept-Charset' => 'utf-8',
                    'Fecha' => $datetime,
                    'Autorizacion' => $this->generateSignHeader($parametersBySign)
                ]
            ]);
        }catch(ClientException $e){
            throw $e;
        }

        $response = \json_decode($response->getBody());

        return $response;

    }

    /**
     * @param string $token El Token de la transaccion
     * @param string $clientId Identificador asignado por la plataforma cliente
     * @param float $mount Monto a cobrar
     * @param array $options Opciones de personalizacion por el momento solo aguanta gateway_type para medio de pago
     * @return Transaction Entrega un objecto con el contenido de la transacción
     */
    public function queryTransaction($token,$clientId,$mount,Array $options = []){

        $mount = number_format($mount, 2, '.', '');
        $client = new Client([
            'base_uri' => $this->url,
        ]);
        $message = [
            'trx_id'=> $clientId,
            'monto' => $mount,
        ];
        if(isset($options['gateway_type'])&&is_int($options['gateway_type'])){
            $message['medio_pago'] = $options['gateway_type'];
        }
        $datetime = (new \DateTime())->format("D, d M Y H:i:s")." GMT";

        $parametersBySign = [
            'transaccion/traer',
            $token,
            $clientId,
            $mount,
            $datetime
        ];

        try{
            /* TODO: reemplazar el GMT por la validacion del formato Datetime::RFC1123
                     ya que servidor de punto pago no aplica correctamente validacion de tiempo con otro timezone
            */
            $response = $client->request('POST','transaccion/'.$token,[
                'json' => $message,
                'headers' => [
                    'Accept'=> 'application/json',
                    "Content-Type" => "application/json; charset=utf-8",
                    'Accept-Charset' => 'utf-8',
                    'Fecha' => $datetime,
                    'Autorizacion' => $this->generateSignHeader($parametersBySign)
                ]
            ]);
        }catch(ClientException $e){
            throw $e;
        }

        $response = \json_decode($response->getBody());

        return $response;

    }

    /**
     * @param $token
     * @param $type
     * @return bool|string
     */
    public function generateUrl($token, $type){
        switch($type){
            case 'process':
                return $this->url.'/transaccion/procesar/'.$token;
            case 'check':
                return $this->url.'/transaccion/'.$token;
            default:
                return false;
        }
    }

    /**
     * @param array $parameters arreglo con los elementos a firmar
     * @return string $signature firma encriptada para su uso
     */
    private function generateSignHeader(array $parameters){
        $msg = implode("\n",$parameters);
        $signature = base64_encode(hash_hmac('sha1', $msg, $this->secret, true));
        $signature = "PP ".$this->key.":".$signature;
        return $signature;
    }
}