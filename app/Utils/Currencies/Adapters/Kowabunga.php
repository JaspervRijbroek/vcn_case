<?php

declare( strict_types=1 );

namespace App\Utils\Currencies\Adapters;

use App\Utils\Currencies\Core\Adapter;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class Kowabunga extends Adapter {
    public $priority = 10;

    public function getClientConfig(): array {
        return [
            'base_uri' => 'http://currencyconverter.kowabunga.net/converter.asmx/'
        ];
    }

    public function getCurrencies(): array {
        $cacheKey = 'currencies.kowabunga';

        if(Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $currencies   = $this->getClient()->get( 'GetCurrencies' );
        $currencies   = $this->parseXML( $currencies->getBody()->getContents() );
        $currencyList = [];

        foreach ( $currencies as $currency ) {
            $currency = (array) $currency;
            $currency = $currency[0];

            $currencyList[ $currency ] = [
                'label'  => false,
                'symbol' => $currency
            ];
        }

        Cache::add($cacheKey, $currencyList, 24 * 60 * 60);

        return $currencyList;
    }

    public function getRate( string $currency, string $currencyTo ) {
        $currencies = $this->getCurrencies();

        if ( ! in_array( $currency, array_column( $currencies, 'symbol' ) ) ) {
            return false;
        }

        $key = [$currency, $currencyTo];
        $cacheKey = 'rate.kowabunga.' . implode('.', $key);

        if(Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $result = $this->getClient()->get( 'GetConversionRate?CurrencyFrom=' . $currency . '&CurrencyTo=' . $currencyTo . '&RateDate=' . date( 'd-m-Y' ) );
        $result = $this->parseXML( $result->getBody()->getContents() );
        $result = (array) $result;

        Cache::add($cacheKey, $result[0], 24 * 60 * 60);

        return $result[0];
    }

    public function getHistory( string $currencyFrom, string $currencyTo, string $dateModifier ) {
        // Get all the dates, for every date one call.
        $key = [$currencyFrom, $currencyTo, $dateModifier];
        $cacheKey = 'history.kowabunga.' . implode('.', $key);

        if(Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $past = Carbon::now()->modify($dateModifier);
        $now = Carbon::now();
        $history = [];

        for($i = $past; $i <= $now; $i = $i->modify('+1 day')) {
            $format = $i->format('m-d-Y');

            $result = $this->getClient()->get('GetConversionRate', [
                'query' => [
                    'CurrencyFrom' => $currencyFrom,
                    'CurrencyTo' => $currencyTo,
                    'RateDate' => $format
                ]
            ]);
            $result = $this->parseXML( $result->getBody()->getContents() );
            $result = (array) $result;

            $history[] = [
                'date' => $format,
                'rate' => $result[0]
            ];
        }

        Cache::add($cacheKey, $history, 24 * 60 * 60);

        return $history;
    }

    private function parseXML( string $xml ) {
        return simplexml_load_string( $xml );
    }

    public function getCurrencyRates( string $base ): array {
        $currencies = $this->getCurrencies();
        $rates = [];

        // We can make a call for every currency here.
        foreach($currencies as $currency) {
            $rates[
                $currency['symbol']
            ] = [
                'rate' => $this->getRate($base, $currency['symbol']),
                'label' => $currency['label']
            ];
        }

        return $rates;
    }
}
