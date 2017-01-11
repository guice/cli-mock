<?php
/**
 * Created by PhpStorm.
 * User: philip
 * Date: 7/20/16
 * Time: 3:39 PM
 */

// Register Commands to Pimple's loader.

/** @var $c Pimple\Container */

$c = require __DIR__ . '/../services.php';
$c = require __DIR__ . '/services.php';

$c['config'] = function ($c) {
    return require __DIR__ . '/config.php';
};

// Commands to register
$c['dataOSC.command.extract'] = function ($c) {
    return new \App\DataOSC\Commands\ExtractCommand($c);
};
$c['dataOSC.command.export'] = function ($c) {
    return new \App\DataOSC\Commands\ExportCommand($c);
};
// Register Commands to global commands structure
$c['console.commands'] = function ($c) {
    return [
        $c['dataOSC.command.extract'],
        $c['dataOSC.command.export']
    ];
};

return $c;
