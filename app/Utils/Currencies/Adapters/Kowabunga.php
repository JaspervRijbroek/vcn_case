<?php

declare( strict_types=1 );

namespace App\Utils\Currencies\Adapters;

use App\Utils\Currencies\Core\Adapter;

class Kowabunga extends Adapter
{
    public $priority = 10;

    public function getClient() {
        return new \SoapClient(
            ...$this->getClientConfig()
        );
    }

    public function getClientConfig(): array {
        return [
            'http://currencyconverter.kowabunga.net/converter.asmx?WSDL',
            []
        ];
    }

    public function getCurrencies(): array {
        // TODO: Implement getCurrencies() method.
    }

    public function getRatesByCurrency(): array {
        // TODO: Implement getRatesByCurrency() method.
    }
}
