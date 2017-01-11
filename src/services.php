<?php

$c = new Pimple\Container();

// Overload this in your service or commands file.
//      This is here to insure 'config' is set to prevent failure in symfony.application call.
$c['config'] = function () {
    return require __DIR__ . '/config.php';
};

$c['service.request'] = function (\Pimple\Container $c) {
    return new \App\Services\Request($c);
};

$c['symfony.log_writer.console'] = function ($c) {
    return new \Symfony\Component\Console\Logger\ConsoleLogger($c['symfony.outputInterface']);
};

$c['service.log_writer.audit'] = function ($c) {
    return new \App\Services\AuditLogger($c);
};

$c['service.log_writer.newrelic'] = function ($c) {
    return new \App\Services\NewRelicLogger($c);
};

$c['service.logger'] = function ($c) {
    return new \App\Middleware\Logger(array($c['symfony.log_writer.console'], $c['service.log_writer.audit'], $c['service.log_writer.newrelic']));
};

$c['service.guzzle.request_hook.debug'] = function ($c) {
    return function (\Psr\Http\Message\RequestInterface $r) use ($c) {
        $logger = $c['service.logger'];
        $logger->info(sprintf("[Guzzle Request] %s %s", $r->getMethod(), $r->getUri()));

        return $r;
    };
};

$c['service.guzzle.response_hook.debug'] = function ($c) {
    return function (\Psr\Http\Message\ResponseInterface $r) use ($c) {
        $logger = $c['service.logger'];
        $logger->info(sprintf("[Guzzle Response] %s", $r->getStatusCode()));

        return $r;
    };
};

$c['service.guzzle'] = function ($c) {

    $stack = \GuzzleHttp\HandlerStack::create();
    $stack->unshift(\GuzzleHttp\Middleware::mapRequest($c['service.guzzle.request_hook.debug']));
    $stack->unshift(\GuzzleHttp\Middleware::mapResponse($c['service.guzzle.response_hook.debug']));

    return new \GuzzleHttp\Client(['handler' => $stack]);
};

$c['service.sftp'] = function ($c) {
    return new \League\Flysystem\Filesystem($c['service.sftp.adapter']);
};

$c['service.ziparchive'] = function ($c) {
    return new \League\Flysystem\Filesystem($c['service.ziparchive.adapter']);
};

$c['service.nelexa.zipoutput'] = function () {
    return new \PhpZip\ZipOutputFile();
};

// League\Csv\ requires the file at creation time. So we'll just set the class name back on this one.
$c['service.csv.reader'] = 'League\Csv\Reader';
$c['service.csv.writer'] = 'League\Csv\Writer';

$c['service.fs'] = function ($c) {
    return new \League\Flysystem\Filesystem($c['service.fs.adapter']);
};

$c['service.fs.adapter'] = function ($c) {

    if ($c['service.fs.adapter.cfg'] == null) {
        throw new \League\Flysystem\Exception('"service.fs.adapter.cfg" configuration value required for Flysystem Local adapter service.');
    }

    return new \League\Flysystem\Adapter\Local($c['service.fs.adapter.cfg']);
};

$c['service.sftp.adapter'] = function ($c) {

    if ( $c['service.sftp.adapter.cfg'] == null ) {
        throw new \League\Flysystem\Exception('"service.sftp.adapter.cfg" configuration value required for Flysystem SFTP service.');
    }

    return new League\Flysystem\Sftp\SftpAdapter($c['service.sftp.adapter.cfg']);
};

$c['service.ziparchive.adapter'] = function ($c) {

    if ( $c['service.ziparchive.adapter.cfg'] == null ) {
        throw new \League\Flysystem\Exception('"service.ziparchive.adapter.cfg" configuration value required for Flysystem ZipArchive service.');
    }

    return new League\Flysystem\ZipArchive\ZipArchiveAdapter($c['service.ziparchive.adapter.cfg']);
};

$c['symfony.application'] = function (\Pimple\Container $c) {
    $cfg = $c['config'];

    $dispatcher = new \Symfony\Component\EventDispatcher\EventDispatcher();
    $dispatcher->addListener(\Symfony\Component\Console\ConsoleEvents::EXCEPTION, function (\Symfony\Component\Console\Event\ConsoleExceptionEvent $event) {
        $command = $event->getCommand();
        $exception = $event->getException();

        $message = sprintf(
            '%s: %s (uncaught exception) at %s line %s while running console command `%s`',
            get_class($exception),
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine(),
            $command->getName()
        );

        if (extension_loaded ('newrelic')) {
            newrelic_notice_error($message, array('exception' => $exception));
        }

        throw $exception;
    });

    $application = new \Symfony\Component\Console\Application($cfg['app_name'], $cfg['app_version']);
    $application->addCommands($c['console.commands']);
    $application->setDispatcher($dispatcher);

    return $application;
};

$c['service.user'] = function ($c) {
    return new \App\Services\User($c);
};

$c['service.message'] = function ($c) {
    return new \App\Services\Message($c);
};

return $c;
