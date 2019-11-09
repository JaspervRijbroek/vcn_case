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
                                <select name="from" id="from">
                                    <option>Please select a currency</option>
                                    @foreach($currencies as $currency)
                                        <option value="{{$currency}}">{{$currency}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-2">
                                <div class="btn btn-primary">
                                    <i class="fa fa-arrows-h btn-sm btn-block" aria-hidden="true"></i>
                                </div>
                            </div>
                            <div class="col-5">
                                <select name="to" id="to">
                                    <option>Please select a currency</option>
                                    @foreach($currencies as $currency)
                                        <option value="{{$currency}}">{{$currency}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>


                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
