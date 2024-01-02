<?php

namespace App\Interfaces;

interface PaymentGateway {
    public function charge($request);
}
