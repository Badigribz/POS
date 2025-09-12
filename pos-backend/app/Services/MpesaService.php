<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MpesaService
{
    private $baseUrl;
    private $shortcode;
    private $passkey;
    private $consumerKey;
    private $consumerSecret;

    public function __construct()
    {
        $this->baseUrl = config('mpesa.base_url');
        $this->shortcode = config('mpesa.shortcode');
        $this->passkey = config('mpesa.passkey');
        $this->consumerKey = config('mpesa.consumer_key');
        $this->consumerSecret = config('mpesa.consumer_secret');
    }

    private function getAccessToken()
    {
        $response = Http::withBasicAuth($this->consumerKey, $this->consumerSecret)
            ->get($this->baseUrl . '/oauth/v1/generate?grant_type=client_credentials');

        return $response->json()['access_token'];
    }

    public function stkPush($phone, $amount, $accountReference = 'GRIBZ SHOP', $transactionDesc = 'POS Sale')
    {
        $timestamp = now()->format('YmdHis');
        $password = base64_encode($this->shortcode . $this->passkey . $timestamp);

        $response = Http::withToken($this->getAccessToken())
            ->post($this->baseUrl . '/mpesa/stkpush/v1/processrequest', [
                "BusinessShortCode" => $this->shortcode,
                "Password" => $password,
                "Timestamp" => $timestamp,
                "TransactionType" => "CustomerPayBillOnline",
                "Amount" => $amount,
                "PartyA" => $phone,
                "PartyB" => $this->shortcode,
                "PhoneNumber" => $phone,
                "CallBackURL" => env('MPESA_CALLBACK_URL'),

                "AccountReference" => $accountReference,
                "TransactionDesc" => $transactionDesc,
            ]);

        Log::info('M-PESA STK Push Response:', $response->json());

        return $response->json();
    }
}
