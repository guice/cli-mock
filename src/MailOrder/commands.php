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
$c['mailOrder.command.poll'] = function ($c) {
    return new \App\MailOrder\Commands\PollCommand($c);
};
$c['mailOrder.command.export'] = function ($c) {
    return new \App\MailOrder\Commands\ExportCommand($c);
};
$c['mailOrder.command.import'] = function ($c) {
    return new \App\MailOrder\Commands\ImportCommand($c);
};
$c['mailOrder.command.process'] = function ($c) {
    return new \App\MailOrder\Commands\ProcessCommand($c);
};

// Register Commands to global commands structure
$c['console.commands'] = function ($c) {
    return [
        $c['mailOrder.command.poll'],
        $c['mailOrder.command.export'],
        $c['mailOrder.command.import'],
        $c['mailOrder.command.process'],
    ];
};

return $c;
