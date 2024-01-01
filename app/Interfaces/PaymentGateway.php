<?php

namespace App\Interfaces;

interface PaymentGateway {
    public function chargeCustomerUsingStripe($request);
    public function chargeCustomerUsingAuthorize($request);
}
