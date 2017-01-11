<?php

$c['dataOSC.service.extract_orders'] = function ($c) {
    return new \App\DataOSC\Services\ExtractOrders($c);
};

$c['dataOSC.service.export'] = function ($c) {
    return new \App\DataOSC\Services\Export($c);
};

$c['dataOSC.service.orders'] = function ($c) {
    return new \App\DataOSC\Services\OrdersService($c);
};

$c['dataOSC.repository.orders'] = function ($c) {
    return new \App\DataOSC\Repository\OrdersRepository($c);
};

// Overloading sftp Adapter with Dual Factor adapter to fix a bug in the SFTP due to 2 step authentication
$c['service.sftp.adapter'] = function ($c) {

    if ( $c['service.sftp.adapter.cfg'] == null ) {
        throw new \League\Flysystem\Exception('"service.sftp.adapter.cfg" configuration value required for Flysystem SFTP service.');
    }

    return new \App\Services\DualFactorSftpAdapter($c['service.sftp.adapter.cfg']);
};

// Overloading 'config' with a combined config
$c['config'] = function ($c) {
    return require __DIR__ . '/config.php';
};

return $c;