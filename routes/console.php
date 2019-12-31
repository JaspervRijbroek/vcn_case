<?php

use Illuminate\Foundation\Inspiring;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->describe('Display an inspiring quote');

Artisan::command('import:rates', function() {
    $result = \App\Utils\Currencies\Currencies::importRates();

    if($result) {
        $this->comment('Currencies Imported');
    } else {
        $this->comment('Issue while importing currencies.');
    }
})->describe('Import all the conversion rates');
