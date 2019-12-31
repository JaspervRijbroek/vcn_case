<?php

declare( strict_types=1 );

namespace App\Utils\Currencies\Core;

use GuzzleHttp\Client;

abstract class Adapter {
    public $priority = 1;
    protected $client;

    public function getClient() {
        if(!$this->client) {
            $this->client = new Client(
                $this->getClientConfig()
            );
        }

        return $this->client;
    }

    public abstract function getClientConfig(): array;

    public abstract function getCurrencies(): array;

    public abstract function getRate(string $currency, string $currencyTo);

    public abstract function getHistory(string $currencyFrom, string $currencyTo, string $dateModifier);

    public abstract function getCurrencyRates(string $base): array;
}
