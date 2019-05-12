<?php

namespace App\Interfaces;

use Illuminate\Http\Request;
use App\Http\Requests\UnsubscribePaymentRequest;

interface PaymentInterface {

    public function charge(Request $request); 
    
    public function unsubscribePayment(UnsubscribePaymentRequest $request); 
}

