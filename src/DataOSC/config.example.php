<?php

return array_replace_recursive(require __DIR__ . '/../config.php', [
    'app_name'    => 'Commands CLI Interface',
    'app_version' => 'v0.1',

    'endpoints' => [
        'search' => '/orders/search',
        'order_details' => '/orders/:id',
        'order_notes' => '/orders/:id/notes',
        'user_details' => '/users/:id/details',
        'responses'    => '/orderlines/:id/response',
    ],

    'sftp' => [
        'host'       => 'secure.dataOSC.org',
        'port'       => 22,
        'username'   => 'defuser',
        'password'   => '',
        'timeout'    => 5,
    ],
    'zip_password' => '',

    'clients' => [
        'pp' => [
            'dirs'        => [
                'base_dir' => '/var/data/pp/files/dataOSC',
                'export_dir' => 'exports',
                'completed_dir' => 'sent',
            ],
            'remote_dirs' => [
                'orders_export_dir' => 'OrderFiles/QAPending',
                'messages_export_dir' => 'Secure_Messages/QAPending',
            ],
        ],
    ],
]);