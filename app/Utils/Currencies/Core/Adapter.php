<?php

declare( strict_types=1 );

namespace App\Utils\Currencies\Core;

use GuzzleHttp\Client;

abstract class Adapter {
    public $priority = 1;

    public function getClient() {
        return new Client(
            $this->getClientConfig()
        );
    }

    public abstract function getClientConfig(): array;

    public abstract function getCurrencies(): array;

    public abstract function getRatesByCurrency(): array;
}
