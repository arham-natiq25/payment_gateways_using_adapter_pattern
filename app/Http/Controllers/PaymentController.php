<?php

namespace App\Http\Controllers;

use App\Adapters\PaymentAdapter;
use App\Gateways\StripePaymentGateway;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    function index(Request $request) {
      $result =  $this->charge($request);

      return $result;

    }

    private function charge($request) {

        $stripe = new StripePaymentGateway();
        $charge = new PaymentAdapter($stripe);
        $test = $charge->chargeCustomer($request);

        return $test;



    }
}
