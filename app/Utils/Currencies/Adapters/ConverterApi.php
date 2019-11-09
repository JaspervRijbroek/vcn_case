<?php

declare( strict_types=1 );

namespace App\Utils\Currencies\Adapters;

use App\Utils\Currencies\Core\Adapter;

class ConverterApi extends Adapter
{
    public function getClientConfig(): array {
        return [
            'base_url' => 'https://free.currconv.com/api/v7/',
            'defaults' => [
                'query' => [
                    'apiKey' => config('currencies.converter-api.apiKey')
                ]
            ]
        ];
    }

    public function getCurrencies(): array {
        $currencies = $this->getClient()->get('currencies');
    }

    public function getRatesByCurrency(): array {

    }
}
