<?php

namespace App\Adapters;

use App\Gateways\AuthorizenetPaymentGateway;
use App\Gateways\StripePaymentGateway;
use App\Interfaces\PaymentGateway;

class PaymentAdapter implements PaymentGateway {

    private $stripe;
    private $authorize;
    public function __construct(StripePaymentGateway $stripe, AuthorizenetPaymentGateway $authorize ) {
        $this->stripe = $stripe ;
        $this->authorize=$authorize;

    }
    function chargeCustomerUsingStripe($request) {

      return  $this->stripe->chargeUsingStripe($request);
    }

    public function chargeCustomerUsingAuthorize($request){
        return $this->authorize->chargeUsingAuthorizenet($request);
    }
}
