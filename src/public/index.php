<?php
//use \Psr\Http\Message\ServerRequestInterface as Request;
//use \Psr\Http\Message\ResponseInterface as Response;

require '../vendor/autoload.php';

$settings = require __DIR__ . '/../settings.php';
$app = new \Slim\App($settings);
//unset($app->getContainer()['errorHandler']);
//unset($app->getContainer()['phpErrorHandler']);

// Set up dependencies
require __DIR__ . '/../dependencies.php';

// Register routes
require __DIR__ . '/../routes.php';

$app->run();
