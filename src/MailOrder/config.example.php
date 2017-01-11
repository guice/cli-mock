<?php

return array_replace_recursive(require __DIR__ . '/../config.php', [
    'app_name'    => 'MailOrder CLI Interface',
    'app_version' => 'v0.1',

    'endpoints' => [
        'check_for_transactions' => '/prescriptions/fills',
        'settle_transaction'     => '/prescriptions/fills/:fill_id/transaction',
        'update_transaction'     => '/prescriptions/fills/:fill_id',
        'generate_file_for'      => '/mailorder/mailOrder/files',
    ],

    'sftp' => [
        'host'       => '',
        'port'       => 2202,
        'username'   => '',
        'password'   => '',
        'privateKey' => '/private.key',
        'root'       => '/var/data',
        'timeout'    => 5,
    ],

    'clients' => [
        'pp' => [
            'dirs'        => [
                'base_dir' => '/var/data/pp/files/mailOrder/',
                'export_dir' => 'exports',
                'incoming_dir' => 'incoming',
                'completed_dir' => 'completed',
            ],
            'remote_dirs' => [
                'export_dir' => 'To_OFS',
                'import_dir' => 'From_OFS',
                'archive_ofs' => 'ORS_Archive',
            ],
        ],
    ],
]);