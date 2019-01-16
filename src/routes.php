<?php
use Slim\Http\Request;
use Slim\Http\Response;

$app->get('/users/{sid}', 'Controller\UserController:Sid');

$app->post('/users/{sid}', 'Controller\UserController:Update');

$app->get('/users', 'Controller\UserController:All');

$app->post('/users', 'Controller\UserController:Add');

$app->post('/pay/{sid}', 'Controller\UserController:InsertPayRecord');