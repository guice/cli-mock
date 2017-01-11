<?php


$c['mailOrder.service.export'] = function ($c) {
    return new \App\MailOrder\Services\Export($c);
};

$c['mailOrder.service.transactions'] = function ($c) {
    return new \App\MailOrder\Services\Transactions($c);
};

$c['mailOrder.service.import'] = function ($c) {
    return new \App\MailOrder\Services\Import($c);
};

// Overloading sftp Adapter with Dual Factor adapter to fix a bug in the SFTP due to 2 step authentication
$c['service.sftp.adapter'] = function ($c) {

    if ( $c['service.sftp.adapter.cfg'] == null ) {
        throw new \League\Flysystem\Exception('"service.sftp.adapter.cfg" configuration value required for Flysystem SFTP service.');
    }

    return new \App\Services\DualFactorSftpAdapter($c['service.sftp.adapter.cfg']);
};

// Overloading 'config' with a combined MailOrder config
$c['config'] = function () {
    return require __DIR__ . '/config.php';
};

return $c;