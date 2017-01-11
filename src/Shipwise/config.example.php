<?php

return array_replace_recursive(require __DIR__ . '/../config.php', [
    'app_name'    => 'MailOrder CLI Interface',
    'app_version' => 'v0.1',

    'sqlite' => __DIR__ . '/../data/shipwise.sqlite',
    'googleMaps' => [
        'apiKey' => 'AIzaSyACCF5bX0fp44kJvEB91dQ3h1G37oQlmh8'
    ],
]);