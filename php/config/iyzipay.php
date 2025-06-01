<?php
// Test modu için basit bir sınıf oluştur
class IyzipayOptions {
    private $apiKey;
    private $secretKey;
    private $baseUrl;

    public function setApiKey($apiKey) {
        $this->apiKey = $apiKey;
    }

    public function setSecretKey($secretKey) {
        $this->secretKey = $secretKey;
    }

    public function setBaseUrl($baseUrl) {
        $this->baseUrl = $baseUrl;
    }

    public function getApiKey() {
        return $this->apiKey;
    }

    public function getSecretKey() {
        return $this->secretKey;
    }

    public function getBaseUrl() {
        return $this->baseUrl;
    }
}

// Test modu yapılandırması
$options = new IyzipayOptions();
$options->setApiKey(null);
$options->setSecretKey(null);
$options->setBaseUrl('https://sandbox-api.iyzipay.com');

return $options; 