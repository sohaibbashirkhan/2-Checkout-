<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Payment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Twocheckout;
use Twocheckout_Charge;

class PaymentController extends Controller
{
    public function index()
    {
        $payments = Payment::where('user_id', Auth::id())->get();
        return view('payments.index', compact('payments'));
    }

    public function store(Request $request)
    {
        // Validate the request data
        $request->validate([
            'name' => 'required',
            'business_name' => 'required',
            'amount' => 'required|numeric'
        ]);

        // Create a new Payment instance with validated data and associate it with the authenticated user
        $payment = new Payment();
        $payment->name = $request->name;
        $payment->business_name = $request->business_name;
        $payment->amount = $request->amount;
        $payment->user_id = Auth::id();
        $payment->status = 'Pending';
        $payment->save();

        // Generate 2Checkout payment link
        $paymentLink = $this->generate2CheckoutLink($payment);

        // Redirect to the payments page with a success message
        return redirect('/payments')->with('success', 'Payment created. Pay here: ' . $paymentLink);
    }

    public function pay($id)
    {
        $payment = Payment::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        $paymentLink = $this->generate2CheckoutLink($payment);
        return view('payments.pay', compact('payment', 'paymentLink'));
    }

    public function webhook(Request $request)
    {
        // Handle 2Checkout webhook
        $data = $request->all();

        // Log webhook data
        Log::info('2Checkout Webhook Received:', $data);

        $payment = Payment::where('id', $data['invoice_id'])->first();
        if ($payment) {
            // Check if payment is successful
            if ($data['invoice_status'] === 'approved') {
                // Update payment status to 'success'
                $payment->status = 'success';
            } else {
                // Update payment status to 'failed' or any other appropriate status
                $payment->status = 'failed';
            }
            $payment->save();
        }

        return response()->json(['status' => 'success']);
    }

    private function generate2CheckoutLink(Payment $payment)
    {
        try {
            // Determine if sandbox mode is enabled
            $isSandbox = env('2CHECKOUT_ENV') === 'sandbox';

            // Fetch the appropriate credentials based on sandbox or live mode
            if ($isSandbox) {
                $sellerId = env('2CHECKOUT_SANDBOX_SELLER_ID');
                $privateKey = env('2CHECKOUT_SANDBOX_PRIVATE_KEY');
                $publishableKey = env('2CHECKOUT_SANDBOX_PUBLIC_KEY');
            } else {
                $sellerId = env('2CHECKOUT_SELLER_ID');
                $privateKey = env('2CHECKOUT_PRIVATE_KEY');
                $publishableKey = env('2CHECKOUT_PUBLIC_KEY');
            }

            // Set 2Checkout SDK credentials
            Twocheckout::privateKey($privateKey);
            Twocheckout::sellerId($sellerId);

            $params = [
                "merchantOrderId" => $payment->id,
                "currency" => "USD",
                "total" => $payment->amount,
                "billingAddr" => [
                    "name" => $payment->name,
                    "addrLine1" => "123 Test St",
                    "city" => "Columbus",
                    "state" => "OH",
                    "zipCode" => "43123",
                    "country" => "USA",
                    // Add more billing details as needed
                ]
            ];

            // Generate a random token or identifier for this payment
            $token = $this->generateRandomToken();

            // Associate the token with the payment in your database
            $payment->update(['payment_token' => $token]);

            // Add the token to payment parameters
            $params['token'] = $token;

            $charge = Twocheckout_Charge::auth($params);

            if (isset($charge['response']['paymentUrl'])) {
                return $charge['response']['paymentUrl'];
            } else {
                Log::error('2Checkout API Error: Payment URL not found in response.');
                return 'Error: Unable to generate payment link.';
            }
        } catch (\Exception $e) {
            Log::error('2Checkout API Error: ' . $e->getMessage());
            return 'Error: Unable to generate payment link.';
        }
    }

    private function generateRandomToken()
    {
        // Generate a random token or identifier using a secure method
        return bin2hex(random_bytes(16));
    }
}
