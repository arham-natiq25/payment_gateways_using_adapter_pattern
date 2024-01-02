<?php

namespace App\Http\Controllers;

use App\Adapters\PaymentServices;
use App\Gateways\AuthorizenetPaymentGateway;
use App\Gateways\StripePaymentGateway;
use App\Models\Setting;
use App\Services\PaymentServices as ServicesPaymentServices;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    function index(Request $request) {

      $result =  $this->charge($request);
      return $result;
    }

    private function charge($request) {
        $charge = new ServicesPaymentServices($request);
       $res = $charge->chargeACustomer();
       return $res;

    }
}
