<?php

namespace App\Http\Services\ApiServices;

class AccountingSecretKeyService
{
    protected $apiUrl;
    protected $authToken;

    public function __construct()
    {
        $this->authToken = config('services.sigma.secret_key');
        $this->apiUrl = config('services.url.accounting_api');
    }

    public function syncAll()
    {
        return true;
    }
}
