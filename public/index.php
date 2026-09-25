<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use DI\ContainerBuilder;
use Developful\Zugzwang\Routes;
use Dotenv\Dotenv;
use Slim\Factory\AppFactory;

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();

$containerBuilder = new ContainerBuilder();

if (($_ENV['APP_ENV'] ?? false) === 'production') {
    $containerBuilder->enableCompilation(__DIR__ . '/../var/cache');
}
/** @var \Psr\Container\ContainerInterface $container */
$container = $containerBuilder->build();
AppFactory::setContainer($container);
/** @var \Slim\App<\Psr\Container\ContainerInterface> $app */
$app = AppFactory::create();

Routes::register($app);

$app->run();
