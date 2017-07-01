<?php

// Timezone.
date_default_timezone_set('Europe/Paris');

// Cache
// $app['cache.path'] = __DIR__ . '/../cache';

// Twig cache
// $app['twig.options.cache'] = $app['cache.path'] . '/twig';

// Doctrine (db)
$app['db.options'] = array(
    'driver'   => 'pdo_mysql',
    'host'     => '127.0.0.1',
    'port'     => null,
    'dbname'   => 'proverbius',
	'charset'  => 'utf8',
    'password' => null,
);