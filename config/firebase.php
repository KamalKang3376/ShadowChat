<?php

return [
    'credentials' => env(
        'FIREBASE_CREDENTIALS',
        storage_path('app/firebase/service-account.json')
    ),

    'database_url' => env('FIREBASE_DATABASE_URL'),
];