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
        if (empty($this->authToken)) {
            throw new \InvalidArgumentException('SECRET KEY is not configured');
        }
        if (empty($this->apiUrl)) {
            throw new \InvalidArgumentException('Accounting API URL is not configured');
        }
    }

    public function syncAll()
    {
        return true;
    }
}
