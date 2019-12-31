<?php

declare( strict_types=1 );

namespace App\Utils\Currencies\Adapters;

use App\Utils\Currencies\Core\Adapter;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class ConverterApi extends Adapter {
    public function getClientConfig(): array {
        return [
            'base_uri' => 'https://free.currconv.com/api/v7/',
            'query'    => [
                'apiKey' => config( 'currencies.converter-api.apiKey' )
            ]
        ];
    }

    public function getCurrencies(): array {
        $cacheKey = 'currencies.currconv';

        if(Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $currencies = $this->getClient()->get( 'currencies' );
        $currencies = json_decode( $currencies->getBody()->getContents(), true );

        if ( ! isset( $currencies['results'] ) || ! count( $currencies['results'] ) ) {
            return [];
        }

        $result = array_map( function ( $currency ) {
            return [
                'label'  => $currency['currencyName'],
                'symbol' => $currency['id']
            ];
        }, $currencies['results'] );

        Cache::add($cacheKey, $result, 24 * 60 * 60);

        return $result;
    }

    public function getRate( string $currency, string $currencyTo ) {
        $currencies = $this->getCurrencies();

        if ( ! in_array( $currency, array_column( $currencies, 'symbol' ) ) ) {
            return false;
        }

        $key = [$currency, $currencyTo];
        $cacheKey = 'rate.currconv.' . implode('.', $key);

        if(Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $convertKey = $currency . '_' . $currencyTo;
        $response   = $this->getClient()->get( 'convert', [
            'query' => array_merge(
                $this->getClient()->getConfig( 'query' ),
                [
                    'q' => $convertKey
                ]
            )
        ] );
        $response   = json_decode( $response->getBody()->getContents(), true );

        Cache::add($cacheKey, $response['results'][ $convertKey ]['val'], 24 * 60 * 60);

        return $response['results'][ $convertKey ]['val'];
    }

    public function getHistory( string $currencyFrom, string $currencyTo, string $dateModifier ) {
        $key = [$currencyFrom, $currencyTo, $dateModifier];
        $cacheKey = 'history.currconv.' . implode('.', $key);

        if(Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $query = $currencyFrom . '_' . $currencyTo;
        $response   = $this->getClient()->get( 'convert', [
            'query' => array_merge(
                $this->getClient()->getConfig( 'query' ),
                [
                    'q' => $query,
                    'date' => Carbon::now()->modify($dateModifier)->format('Y-m-d'),
                    'endDate' => Carbon::now()->format('Y-m-d')
                ]
            )
        ] );
        $response   = json_decode( $response->getBody()->getContents(), true );

        $history = [];

        foreach($response['results'][$query]['val'] as $date => $rate) {
            $history[] = [
                'date' => $date,
                'rate' => $rate
            ];
        }

        Cache::add($cacheKey, $history, 24 * 60 * 60);

        return $history;
    }

    public function getCurrencyRates( string $base ): array {
        $currencies = $this->getCurrencies();
        $chunks = array_chunk($currencies, 10);
        $rates = [];

        foreach($chunks as $chunk) {
            // Collect all the currencies here.
            $queries = collect($chunk)->map(function($chunkItem) use ($base) {
                return $base . '_' . $chunkItem['symbol'];
            });

            $response = $this->getClient()->get( 'convert', [
                'query' => array_merge(
                    $this->getClient()->getConfig( 'query' ),
                    [
                        'q' => $queries->join(',')
                    ]
                )
            ] );
            $response = json_decode($response->getBody()->getContents(), true);

            foreach($response['results'] as $result) {
                $rates[$result['to']] = [
                    'rate' => $result['val'],
                    'label' => collect($currencies)->first(function($currency) use ($result) {
                        return $currency['symbol'] === $result['to'];
                    })['label']
                ];
            }
        }

        return $rates;
    }
}
