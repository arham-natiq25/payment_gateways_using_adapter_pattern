<?php

namespace App\Services;

use App\Gateways\AuthorizenetPaymentGateway;
use App\Gateways\StripePaymentGateway;
use App\Interfaces\PaymentGateway;
use App\Models\Setting;

class PaymentServices {

   protected $data;
    public function __construct($request) {
        $this->data=$request;
    }

    function chargeACustomer() {
        $setting = Setting::find(1);
        if ($setting===null || $setting->active_gateway===1) {
            $gateway = new StripePaymentGateway();
           return $gateway->charge($this->data);
        }elseif ($setting->active_gateway===0) {
            $gateway = new AuthorizenetPaymentGateway();
            return  $gateway->charge($this->data);
            }
    }
}
