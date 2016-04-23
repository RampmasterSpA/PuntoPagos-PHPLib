<?php
/**
 * Created by PhpStorm.
 * User: rmorenp
 * Date: 23-04-16
 * Time: 6:09 PM
 */

namespace PuntoPagos;


class Transaction
{
    //Manejo de errores
    const NO_ERROR = 0;
    const ERROR_REJECT = 1;
    const ERROR_ABORT = 2;
    const ERROR_INCOMPLETE = 6;
    const ERROR_GATEWAY = 7;

    //Pasarela de pagos
    const GW_PRESTO = 2;
    const GW_WEBPAY = 3;
    const GW_BCHILE = 4;
    const GW_BCI = 5;
    const GW_TBANC = 6;
    const GW_BESTADO = 7;
    const GW_BBVA = 16;
    const GW_RIPLEY = 10;
    const GW_PAYPAL = 15;

    //Variables del objeto
    private $responseCode = 0;
    private $token = null;
    private $txId = 0;
    private $gatewayId = 0;
    private $mount = 0;
    private $url = 'https://www.puntopagos.com';

    /**
     * @return bool
     */
    public function isToken(){
        return ($this->token != null);
    }

}