<?php

namespace App\Adapters;

use App\Gateways\StripePaymentGateway;
use App\Interfaces\PaymentGatewayStripe;

class PaymentAdapter implements PaymentGatewayStripe {

    private $stripe;
    public function __construct(StripePaymentGateway $stripe) {
        $this->stripe = $stripe ;
    }
    function chargeCustomer($request) {

      return  $this->stripe->chargeUsingStripe($request);
    }
}
