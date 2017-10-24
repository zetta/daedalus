<?php

/**
 * Dependency Injection
 */

use Silex\Application;
use Silex\Provider\AssetServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\ServiceControllerServiceProvider;
use Silex\Provider\HttpFragmentServiceProvider;

use Daedalus\Catalog\RouteCatalog;
use Daedalus\Services\RouteService;
use Daedalus\Services\PathFinderService;
use Daedalus\Validator\RouteInputValidator;

use Http\Adapter\Guzzle6\Client;
use Http\Message\MessageFactory\GuzzleMessageFactory;
use Ivory\GoogleMap\Service\DistanceMatrix\DistanceMatrixService;

use Daedalus\Delegator\BackgroundProcessor;

$app = new Application();
$app->register(new ServiceControllerServiceProvider());
$app->register(new HttpFragmentServiceProvider());

$app['route.catalog'] = function() use ($app) {
    return (new RouteCatalog())
        ->setConnection($app['db']);
};

// this needs to be proxied to avoid performance issues on dependecy injection
$app['google.distance.matrix.service'] = function() use ($app) {
    return new DistanceMatrixService(new Client(), new GuzzleMessageFactory());
};

$app['route.service'] = function() use ($app) {
    return (new RouteService())
        ->setPathFinderService($app['path.finder.service'])
        ->setCatalog($app['route.catalog']);
};

$app['path.finder.service'] = function() use ($app) {
    return (new PathFinderService())
        ->setContainer($app)
        //->setDistanceMatrixService($app['google.distance.matrix.service']) lazy loaded
    ;
};

$app['background.processor'] = function() use ($app) {
    return new BackgroundProcessor();
};

$app['route.input.validator'] = function() use ($app) {
    return new RouteInputValidator();
};

$app->register(new Silex\Provider\DoctrineServiceProvider(), [
    'db.options' => [
        'driver' => 'pdo_mysql',
        'host' => getenv('MYSQL_HOST'),
        'user' => getenv('MYSQL_USER'),
        'dbname' => getenv('MYSQL_DATABASE'),
        'password' => getenv('MYSQL_PASSWORD'),
        'charset' => 'utf8',
    ],
]);

return $app;
