<?php

declare( strict_types=1 );

namespace App\Utils\Currencies;

class Currencies {
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

    /**
     * This method will call the provided method on every following adapter.
     * It will first try to call it on the first, if it fails, it will call it on the next.
     *
     * This will continue until we have a success or we run out of adapters,
     * if that happens we will return a false.
     *
     * @param string $method The method to call on the adapter.
     * @param mixed ...$arguments Other arguments the called method might need.
     */
    private function chainCall($method, ...$arguments) {
        $result = false;

        foreach($this->getAdapters() as $adapter) {
            try {
                if($result = call_user_func_array([$adapter, $method], $arguments)) {
                    break;
                }
            } catch(\Exception $e) {
                // NOOP
            } catch (\Error $e) {
                // NOOP
            }
        }

        return $result;
    }

    public static function importRates() {
        // Get all the adapters.
        $instance = new Currencies();
        $rates = $instance->chainCall('getRates');

        echo '<pre>';
            print_r($rates);
        echo '</pre>';
        die();
    }
}
