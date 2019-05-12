<?php

namespace App\Interfaces;
use App\Http\Requests\NotificationReadRequest;
use Illuminate\Http\Request;

interface NotificationInterface {

    public function readNotification(NotificationReadRequest $request);
    public function getNotifications(Request $request);
    
}

