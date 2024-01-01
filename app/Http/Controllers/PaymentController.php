<?php

namespace App\Http\Controllers;

use App\Adapters\PaymentAdapter;
use App\Gateways\AuthorizenetPaymentGateway;
use App\Gateways\StripePaymentGateway;
use App\Models\Setting;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    function index(Request $request) {

      $result =  $this->charge($request);

      return $result;

    }

    private function charge($request) {

        $setting = Setting::find(1);


        $stripe = new StripePaymentGateway();
        $authorize = new AuthorizenetPaymentGateway();
        $charge = new PaymentAdapter($stripe,$authorize);
        if ($setting===null || $setting->active_gateway===1) {
            $test = $charge->chargeCustomerUsingStripe($request);
        }elseif ($setting->active_gateway===0) {
            $test = $charge->chargeCustomerUsingAuthorize($request);
        }else{
            $test=$charge->chargeCustomerUsingStripe($request);
        }


        return $test;



    }
}
