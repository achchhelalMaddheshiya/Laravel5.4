<?php
if (!empty($_SERVER['APP_ENV']) && $_SERVER['APP_ENV'] == 'local') {
    return [
        'SITE_NAME' => 'Willodiary',
        'ADMIN_EMAIL' => 'mukesh@ignivasolutions.com',
        'SERVER_URL' => 'http://server.willodiary.com',
        'ADMIN_URL' => 'http://admin.willodiary.com',
        'FRONTEND_URL' => 'http://localhost:4200',
        'WEBHOOK_TOKEN' => 'whsec_irQw6L2vHOnPK6fFKkHDWchOuB3bsBR3',
        'PER_PAGE' => 10,
    ];
} else {
    return [
        'SITE_NAME' => 'Willodiary',
        'ADMIN_EMAIL' => 'mukesh@ignivasolutions.com',
        'SERVER_URL' => 'http://willodiaryserver.ignivastaging.com',
        'ADMIN_URL' => 'http://admin.willodiary.com',
        'FRONTEND_URL' => 'http://willodiary.ignivastaging.com',
        'PER_PAGE' => 5,
        'WEBHOOK_TOKEN' => 'whsec_irQw6L2vHOnPK6fFKkHDWchOuB3bsBR3',
    ];
}
