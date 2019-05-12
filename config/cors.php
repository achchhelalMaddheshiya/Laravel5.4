<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Laravel CORS
    |--------------------------------------------------------------------------
    |
    | allowedOrigins, allowedHeaders and allowedMethods can be set to array('*')
    | to accept any value.
    |
    */
   
    'supportsCredentials' => false,
    'allowedOrigins' => ['*'],
    'allowedOriginsPatterns' => [],
    'allowedHeaders' => ['Content-Type', 'X-Auth-Token', 'Origin','Authorization', 'X-Requested-With','Token','Accept'],
    'allowedMethods' => ['GET', 'POST', 'PUT','DELETE','OPTIONS'],
    'exposedHeaders' => ['access_token','Content-Type', 'X-Auth-Token','Token', 'Origin','Authorization', 'X-Requested-With','Accept','Allow','content-length'],
    'maxAge' => 0,

];
