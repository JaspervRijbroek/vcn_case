<?php

declare( strict_types=1 );

namespace App\Utils\Currencies;

use App\conversionRate;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class Currencies {
    static $instance;

    /**
     * This method will get all the currency adapters.
     * As we will only ever interface with this class and not directly with the adapters.
     *
     * @return array The adapters ordered by priority.
     */
    public function getAdapters(): array {
        $adapters = glob(__DIR__ . '/Adapters/*.php');
        $adapters = array_map(function($adapterPath) {
            // Get the filename.
            $filename = str_replace('.php', '', basename($adapterPath));
            $classname = __NAMESPACE__ . '\\Adapters\\' . $filename;

            return new $classname;
        }, $adapters);

        usort($adapters, function($a, $b) {
            if($a->priority == $b->priority) {
                return 0;
            } if ($a->priority < $b->priority) {
                return -1;
            }

            return 1;
        });

        return $adapters;
    }

    public function getCurrencies(): array {
        if(Cache::has('currencies')) {
            return Cache::get('currencies');
        }

        $currencies = $this->serialCall('getCurrencies');
        $currencyList = [];

        foreach($currencies as $registry) {
            foreach($registry as $currency) {
                if(!isset($currencyList[$currency['symbol']])) {
                    $currencyList[$currency['symbol']] = $currency;
                }

                if(!$currencyList[$currency['symbol']]['label'] && $currency['label']) {
                    $currencyList[$currency['symbol']]['label'] = $currency['label'];
                }
            }
        }

        $currencyList = array_filter($currencyList, function($currency) {
            return isset($currency['label']) && !empty($currency['label']) && $currency['label'];
        });

        usort($currencyList, function($a, $b) {
            return strcmp($a['label'], $b['label']);
        });

        Cache::add('currencies', $currencyList, 24 * 60 * 60);

        return $currencyList;
    }

    public function getRate(string $from, string $to) {
        return $this->chainCall('getRate', $from, $to);
    }

    public function getHistory(string $currencyFrom, string $currencyTo, string $dateModifier) {
        return $this->chainCall('getHistory', $currencyFrom, $currencyTo, $dateModifier);
    }

    public static function getInstance() {
        if(!self::$instance) {
            self::$instance = new Currencies();
        }

        return self::$instance;
    }

    public static function importRates(): bool {
        $instance = self::getInstance();
        $currencies = $instance->serialCall('getCurrencyRates', 'EUR');
        $currencyList = [];

        if(!count($currencies)) {
            return false;
        }

        $currencies = array_filter($currencies, function($list) {
            return !!$list;
        });

        foreach($currencies as $registry) {
            foreach($registry as $symbol => $value) {
                if(!isset($currencyList[$symbol])) {
                    $currencyList[$symbol] = $value;
                }

                if(!$currencyList[$symbol]['label'] && $value['label']) {
                    $currencyList[$symbol]['label'] = $value['label'];
                }
            }
        }

        $currencyList = array_filter($currencyList, function($currency) {
            return isset($currency['label']) && !empty($currency['label']) && $currency['label'];
        });

        usort($currencyList, function($a, $b) {
            return strcmp($a['label'], $b['label']);
        });

        // We use EUR as base currency. So all rates will get be based on EUR.
        foreach($currencies as $symbol => $rate) {
            conversionRate::create([
                'currency' => $symbol,
                'rate' => $rate['rate'],
                'label' => $rate['label']
            ]);
        }

        return true;
    }

    /**
     * This method will call the provided method on every following adapter.
     * It will first try to call it on the first, if it fails, it will call it on the next.
     *
     * This will continue until we have a success or we run out of adapters,
     * if that happens we will return a false.
     *
     * @param string $method The method to call on the adapter.
     * @param mixed ...$arguments Other arguments the called method might need.
     *
     * @return bool|mixed The result of the first matching call.
     */
    private function chainCall($method, ...$arguments) {
        $result = false;

        foreach($this->getAdapters() as $adapter) {
            try {
                $response = call_user_func_array([$adapter, $method], $arguments);
                if($response) {
                    $result = $response;
                    break;
                }
            } catch(\Exception $e) {
                // NOOP
                continue;
            } catch (\Error $e) {
                // NOOP
                continue;
            }
        }

        return $result;
    }

    /**
     * This method will call each and every adapter and will return its result.
     *
     * @param string $method The method to call on the adapter.
     * @param mixed ...$arguments Other arguments the called method might need.
     *
     * @return array The result of all the adapters.
     */
    private function serialCall($method, ...$arguments): array {
        $result = [];

        foreach($this->getAdapters() as $adapter) {
            try {
                $result[] = call_user_func_array([$adapter, $method], $arguments);
            } catch(\Exception $e) {
                var_dump($e->getMessage());
                $result[] = false;
            } catch (\Error $e) {
                var_dump($e->getMessage());
                $result[] = false;
            }
        }

        return $result;
    }
}
