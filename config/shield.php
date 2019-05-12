<?php
return [

    'services' => [
        'stripe' => [
            'driver' => \Shield\Stripe\Stripe::class,
            'options' => [
                'secret' => 'whsec_irQw6L2vHOnPK6fFKkHDWchOuB3bsBR3',
                'tolerance' => 300, // in seconds, you can remove this line to use stripes default.
            ],
        ],
    ],

];
