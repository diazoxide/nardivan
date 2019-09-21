<?php

use Composer\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;

putenv('COMPOSER=composer.json');
// call `composer install` command programmatically
$input = new ArrayInput( $_SERVER['argv']);
$application = new Application();
$application->setAutoExit(false);
$application->run($input);