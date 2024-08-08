@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Pay for {{ $payment->business_name }}</h1>
    <p>Amount: ${{ $payment->amount }}</p>
    <a href="{{ $paymentLink }}" class="btn btn-success">Pay Now</a>
</div>
@endsection
