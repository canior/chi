<?php
/**
 * Created by PhpStorm.
 * User: tandy
 * Date: 2018-11-02
 * Time: 10:59 PM
 */

// tests/bootstrap.php
if ($_ENV['BOOTSTRAP_CLEAR_CACHE_ENV']) {
    // executes the "php bin/console cache:clear" command
    passthru(sprintf(
        'php "%s/../bin/console" cache:clear --env=%s --no-warmup',
        __DIR__,
        $_ENV['BOOTSTRAP_CLEAR_CACHE_ENV']
    ));
}

if ($_ENV['DROP_TABLE']) {
    passthru(sprintf(
        'php "%s/../bin/console" doctrine:schema:drop --env=%s --force --full-database',
        __DIR__,
        1
    ));
}

if ($_ENV['DB_MIGRATION']) {
    passthru(sprintf(
        'php "%s/../bin/console" doctrine:migrations:migrate',
        __DIR__,
        1
    ));
}

require __DIR__.'/../vendor/autoload.php';