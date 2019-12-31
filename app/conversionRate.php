<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class conversionRate extends Model
{
    protected $fillable = ['currency', 'rate', 'label'];
    protected $table = 'conversion_rates';
}
