<?php

namespace App\Interfaces;
use App\Http\Requests\ContactUs;
use Illuminate\Http\Request;

interface PagesInterface {

    public function contactUs(ContactUs $request);
    
}

