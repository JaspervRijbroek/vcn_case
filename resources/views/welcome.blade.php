@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-6 offset-3">
                <div class="card">
                    <div class="card-header">
                        Currency converter
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-5">
                                <div class="row">
                                    <select name="from" id="from" class="select2 col-12" data-currency>
                                        <option value="">Please select a currency</option>
                                        @foreach($currencies as $currency)
                                            <option value="{{$currency['symbol']}}">{{$currency['label'] ? $currency['label'] : $currency['symbol']}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="row mt-2">
                                    <input type="text" class="form-control col-12" data-amount-from>
                                </div>
                            </div>
                            <div class="col-2">
                                <div class="btn btn-primary" data-switch>
                                    <i class="fa fa-arrows-h btn-sm btn-block" aria-hidden="true"></i>
                                </div>
                            </div>
                            <div class="col-5">
                                <div class="row">
                                    <select name="to" id="to" class="select2 col-12" data-currency>
                                        <option value="">Please select a currency</option>
                                        @foreach($currencies as $currency)
                                            <option value="{{$currency['symbol']}}">{{$currency['label'] ? $currency['label'] : $currency['symbol']}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="row mt-2">
                                    <input type="text" class="form-control col-12" data-amount-to>
                                </div>
                            </div>
                        </div>

                        <div class="row" data-totals>
                            <div class="col-5">
                                <span data-from></span>/ <span data-to></span>: <span data-total></span>
                            </div>
                            <div class="col-5 offset-2">
                                <span data-to></span>/ <span data-from></span>: <span data-total-inverted></span>
                            </div>
                        </div>

                        <div class="row">
                            <canvas class="col-12 history" data-history></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        jQuery(function($) {
            var $totals = $('[data-totals]'),
                $history = $('[data-history]'),
                rateFrom = 0,
                rateTo = 0,
                chart = null,
                $amountFrom = $('[data-amount-from]'),
                $amountTo = $('[data-amount-to]'),
                $switch = $('[data-switch]');

            $totals.hide();
            $history.hide();

            $switch.on('click', function() {
                var $from = $('#from'),
                    $to = $('#to'),
                    fromVal = $from.val(),
                    toVal = $to.val();

                $from.select2('destroy');
                $to.select2('destroy');

                $from.val(toVal).select2();
                $to.val(fromVal).select2();

                $from.trigger('custom-trigger');
            });

            $amountFrom.on('keyup', function() {
                if(rateFrom && $amountFrom.val()) {
                    $amountTo.val((parseFloat($amountFrom.val()) * rateFrom).toFixed(2));
                }
            });

            $amountTo.on('keyup', function() {
                if(rateTo && $amountTo.val()) {
                    $amountFrom.val((parseFloat($amountTo.val()) * rateTo).toFixed(2));
                }
            });

            $('[data-currency]').on('change custom-trigger', function(event) {
                // If we have two values. Then we need to go.
                var values = $('[data-currency]').toArray().map(function(item) {
                    return $(item).val();
                }).filter(function(value) {
                    return !!value;
                });

                if(values.length !== 2) {
                    // Do nothing.
                    return;
                }

                var currencyFrom = values[0],
                    currencyTo = values[1];

                $.ajax('/api/convert', {
                    method: 'POST',
                    token: '{{csrf_token()}}',
                    data: {
                        symbol_from: currencyFrom,
                        symbol_to: currencyTo
                    },
                    success(data) {
                        $('[data-from]').text(currencyFrom);
                        $('[data-to]').text(currencyTo);

                        rateFrom = parseFloat(data.rate);
                        rateTo = parseFloat(data.rateReversed);

                        $('[data-total]').text(rateFrom.toFixed(4));
                        $('[data-total-inverted]').text(rateTo.toFixed(4));

                        if($amountFrom.val()) {
                            $amountTo.val((parseFloat($amountFrom.val()) * rateFrom).toFixed(2));
                        } else if($amountTo.val()) {
                            $amountFrom.val((parseFloat($amountTo.val()) * rateTo).toFixed(2));
                        }

                        $totals.show();

                        if(chart) {
                            chart.destroy();
                        }

                        chart = new Chart($history.get(0), {
                            type: 'line',
                            data: {
                                labels: data.history.map(function(item) {
                                    return item.date;
                                }),
                                datasets: [{
                                    label: 'Conversion Rate',
                                    data: data.history.map(function(item) {
                                        return item.rate;
                                    }),
                                    backgroundColor: 'blue',
                                    borderColor: 'blue',
                                    fill: false
                                }]
                            }
                        })

                        $history.show();
                    }
                });
                console.log(values);
            });

            $('[data-switch]').on('click', function(event) {
                event.preventDefault();

                var $to = $('#to'),
                    $from = $('#from'),
                    fromValue = $from.val(),
                    toValue = $to.val();

                $from.val(toValue);
                $to.val(fromValue)
            });
        });
    </script>
@endsection
