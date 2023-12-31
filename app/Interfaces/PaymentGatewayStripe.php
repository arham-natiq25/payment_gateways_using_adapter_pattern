<?php

namespace App\Interfaces;

interface PaymentGatewayStripe {
    public function chargeCustomer($request);
}
