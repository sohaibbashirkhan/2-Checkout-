@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Payments</h1>
    <form method="POST" action="/payments" id="payment-form">
        @csrf
        <div class="form-group">
            <label for="name">Name</label>
            <input type="text" class="form-control" id="name" name="name" required>
        </div>
        <div class="form-group">
            <label for="business_name">Business Name</label>
            <input type="text" class="form-control" id="business_name" name="business_name" required>
        </div>
        <div class="form-group">
            <label for="amount">Amount</label>
            <input type="number" class="form-control" id="amount" name="amount" required>
        </div>
        <button type="submit" class="btn btn-primary">Submit</button>
    </form>

    <h2>All Payments</h2>
    <table class="table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Business Name</th>
                <th>Amount</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach($payments as $payment)
            <tr>
                <td>{{ $payment->name }}</td>
                <td>{{ $payment->business_name }}</td>
                <td>{{ $payment->amount }}</td>
                <td>{{ $payment->status }}</td>
                <td><a href="/payment/{{ $payment->id }}/pay" class="btn btn-primary">Pay</a></td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
<input type="hidden" id="token" name="token">

@endsection

@section('scripts')
<script>
document.getElementById('payment-form').addEventListener('submit', function(event) {
    event.preventDefault();

    var paymentForm = this;
    var tokenRequest = {
        sellerId: '{{ env('2CHECKOUT_SELLER_ID') }}',
        publishableKey: '{{ env('2CHECKOUT_PUBLIC_KEY') }}',
        ccNo: document.getElementById('card_number').value,
        expMonth: document.getElementById('card_expiry_month').value,
        expYear: document.getElementById('card_expiry_year').value,
        cvv: document.getElementById('cvv').value
    };

    TCO.requestToken(successCallback, errorCallback, tokenRequest);

    function successCallback(data) {
        document.getElementById('token').value = data.response.token.token;
        paymentForm.submit();
    }

    function errorCallback(data) {
        console.error('Error: ', data.errorCode, data.errorMsg);
        // Handle error, display message to user, etc.
    }
});
</script>
@endsection