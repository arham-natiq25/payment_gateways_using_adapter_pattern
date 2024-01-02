<?php

namespace App\Gateways;

use App\Interfaces\PaymentGateway;
use App\Models\CustomerProfile;
use App\Models\TransactionRecords;
use Illuminate\Support\Facades\Auth;
use Stripe\Customer;
use Stripe\PaymentIntent;
use Stripe\PaymentMethod;
use Stripe\Stripe;

class StripePaymentGateway implements PaymentGateway
{


   
    function charge($request)
    {
        Stripe::setApiKey(config('stripe.stripe_sk'));
        if ($request->method===0) {
            $paymentMethodId = $request->paymentMethodId;
        }
        elseif ($request->method===1) {
            $paymentMethodId=$request->card['payment_method_id'];
            $stripeCustomer=$request->card['customer_profile_id'];
        }
        // dd($paymentMethodId);
        $paymentMethod = PaymentMethod::retrieve($paymentMethodId);
       ;

        // Extract the brand of card
        $cardType = $paymentMethod->card->brand;
        // Extract the last four digits
        $lastFourDigits = $paymentMethod->card->last4;
        $user = Auth::user();



        try {
            // create Stripe Customer in stripe
            if ($request->method===0) {
                $stripeCustomer = Customer::create([
                    'email' => $request->email,
                    'name' => $request->name,
                    'payment_method' => $paymentMethodId,

                ]);
            }
            // save record of card in database

            // Create a PaymentIntent and charge a payment
            $intent = PaymentIntent::create([
                'payment_method' => $paymentMethodId, // from frontend
                'amount' => $request->payment * 1000, // Set the amount to be charged (in cents)
                'currency' => 'usd',
                'confirmation_method' => 'manual', // always manual
                'confirm' => true, // always true,
                'return_url' => 'https://127.0.0.1:8000', // necessary param
                'customer' => $stripeCustomer, // customer data we cretaed in stripe also passes to payment so that payment is chraged
                // for that customer
            ]);
            $chargeId = $intent->latest_charge;

            if($request->method===0){
                CustomerProfile::create([
                    'user_id'=>$user->id,
                    'last_four_digit'=>$lastFourDigits,
                    'customer_profile_id'=>$stripeCustomer->id,
                    'payment_method_id'=>$paymentMethodId,
                    'method'=>'stripe'
                ]);
            }
            TransactionRecords::create([
                'user_id'=>$user->id,
                'payment'=>$request->payment,
                'trx_id'=>$chargeId,
                'method'=>'stripe'
            ]);
            return response()->json(['success' => true, 'message' => 'Payment successfully received']);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
