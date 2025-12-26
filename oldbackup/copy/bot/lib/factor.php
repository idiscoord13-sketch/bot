<?php

defined('BASE_DIR') || die('NO ACCESS');

function factor( $amount, $url = '', $description = '', $email = '', $mobile = '' )
{
    if ( $url == '' )
    {
        $url = URL_VERIFY;
    }
    $data     = array (
        "merchant_id"  => MERCHANT_ID,
        "amount"       => $amount,
        "callback_url" => $url,
        "description"  => $description,
        "metadata"     => [/*"email" => $email,
            "mobile" => $mobile*/
        ],
    );
    $jsonData = json_encode($data);
    $ch       = curl_init('https://api.zarinpal.com/pg/v4/payment/request.json');
    curl_setopt($ch, CURLOPT_USERAGENT, 'ZarinPal Rest Api v1');
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array (
        'Content-Type: application/json',
        'Content-Length: ' . strlen($jsonData)
    ));

    $result = curl_exec($ch);
    $err    = curl_error($ch);
    $result = json_decode($result, true, JSON_PRETTY_PRINT);
    curl_close($ch);


    if ( $err )
    {
        throw new Exception("cURL Error #:" . json_encode($err));
    }
    else
    {
        if ( empty($result['errors']) )
        {
            if ( $result['data']['code'] == 100 )
            {
                return $result['data']["authority"];
            }
            throw new Exception('ZarinPal Error: ' . json_encode($result), $result['data']['code']);
        }
        else
        {
            throw new Exception('Zarinpal Has Error: {' . $result['errors']['code'] . '} [' . $result['errors']['message'] . ']');
        }
    }
}