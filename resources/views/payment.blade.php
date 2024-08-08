<!DOCTYPE html>
<html>
<head>
    <title>Payment</title>
    <script src="https://www.2checkout.com/checkout/api/2co.min.js"></script>
    <script>
        // Function to generate token
        function generateToken() {
            var args = {
                sellerId: "{{ env('2CHECKOUT_SELLER_ID') }}",
                publishableKey: "5401B309-4E7B-4DD4-873A-AC31F357AA76",
                ccNo: "4111111111111111", // Dummy card number for testing
                cvv: "123",
                expMonth: "12",
                expYear: "2024"
            };

            TCO.requestToken(successCallback, errorCallback, args);
        }

        function successCallback(data) {
            var token = data.response.token.token;
            document.getElementById('payment-form').token.value = token;
            document.getElementById('payment-form').submit();
        }

        function errorCallback(data) {
            if (data.errorCode === 200) {
                generateToken();
            } else {
                alert(data.errorMsg);
            }
        }

        TCO.loadPubKey('sandbox'); // Use 'sandbox' for testing and 'production' for live transactions

        document.getElementById('payment-form').onsubmit = function(event) {
            event.preventDefault();
            generateToken();
        };
    </script>
</head>
<body>
    <form id="payment-form" action="{{ route('payments.store') }}" method="POST">
        @csrf
        <input type="hidden" name="token" value="">
        <!-- Include other necessary form fields -->
        <button type="submit">Submit Payment</button>
    </form>
</body>
</html>
