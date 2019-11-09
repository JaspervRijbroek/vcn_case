<?php

namespace Tests\Unit;

use App\Utils\Currencies\Currencies;
use Tests\TestCase;

class CurrenciesTest extends TestCase
{
    // Make sure all the adapters are there.
    public function testCurrencyAdapters() {
        $currencies = new Currencies();
        $adapters = $currencies->getAdapters();

        $this->assertIsArray($adapters);
        $this->assertEquals(2, count($adapters));
    }
}
