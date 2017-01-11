<?php
return [
    'app_name' => 'Scheduled Jobs CLI Interface',
    'app_version' => 'v0.1',

    'clients' => [
        // Code is being written to assume no trailing slashes on URLs.
        'client1' => [
            'apis' => [
                'api' => 'https://dev-client1-api.gpcentre.net',
                'auth' => 'https://dev-client1-auth.gpcentre.net',
            ],
        ],
        'default' => [
            'apis' => [
                'api' => 'https://dev-api.gpcentre.net',
                'auth' => 'https://dev-api.gpcentre.net',
            ],
        ],
    ],

    // Use this for any config specific to third part composer libraries.
    'vendors' => [

    ],

    'svc_login' => [
        'username' => 'system',
        'password' => 'system',
    ],

    'event_runner' => 'https://dev-events.gpcentre.net/',

    'endpoints' => [
        'user_details' => '/users/:user_id/details',
        'templated_messages' => '/templatedmessages'
    ]
];

