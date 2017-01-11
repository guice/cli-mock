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

// Going to get a little crowded here. Hmm.
    $c['shipwise.command.warehouse:create'] = function ($c) {
    return new \GP\Shipwise\Command\Warehouse\Create($c);
};
    $c['shipwise.command.warehouse:list'] = function ($c) {
    return new GP\Shipwise\Command\Warehouse\Listing($c);
};

    $c['shipwise.command.product:create'] = function ($c) {
    return new \GP\Shipwise\Command\Product\Create($c);
};

    $c['shipwise.command.product:list'] = function ($c) {
    return new \GP\Shipwise\Command\Product\Listing($c);
};

    $c['shipwise.command.order:create'] = function ($c) {
    return new \GP\Shipwise\Command\Order\Create($c);
};

// Register Commands to global commands structure
$c['console.commands'] = function ($c) {
    return [
        $c['warehouse:create'],
        $c['warehouse:list'],
        $c['product:create'],
        $c['product:list'],
        $c['order:create'],
    ];
};

return $c;
