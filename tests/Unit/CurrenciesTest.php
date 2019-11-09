<?php

namespace Tests\Unit;

use App\Utils\Currencies\Currencies;
use Tests\TestCase;

class CurrenciesTest extends TestCase
{
    // Make sure all the adapters are there.
    public function testCurrencyAdapters() {
        $currencies = Currencies::getInstance();
        $adapters = $currencies->getAdapters();

        $this->assertIsArray($adapters);
        $this->assertEquals(2, count($adapters));
    }

    public function testCurrencyHistory() {
        $currencies = Currencies::getInstance();
        $history = $currencies->getHistory('EUR', 'USD', '-1 week');
        $twoWeeks = $currencies->getHistory('EUR', 'USD', '-2 weeks');

        $this->assertEquals(8, count($history));
        $this->assertEquals(15, count($twoWeeks));
    }
}
