<?php

use Respect\Validation\Validator as v;

session_start();
date_default_timezone_set("UTC");

require __DIR__ . '/../vendor/autoload.php';

require 'config.php';

$app = new \Slim\App([
    'settings' => [
        'displayErrorDetails' => DEBUG,
        'db' => [
            'driver' => 'mysql',
            'host' => DB_HOST,
            'database' => DB_NAME,
            'username' => DB_USER,
            'password' => DB_PASS,
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix' => '',
        ]
    ],
]);


$container = $app->getContainer();

$db = new MysqliDb (DB_HOST, DB_USER, DB_PASS, DB_NAME);
$db->setPrefix ('ip_');

$container['db'] = function ($container) use ($db) {
    return $db;
};

$container['flash'] = function ($container) {
    return new \Slim\Flash\Messages;
};

$container['view'] = function ($container)  {
    $view = new \Slim\Views\Twig(__DIR__ . '/../resources/views', [
        'cache' => false,
    ]);

    $view->addExtension(new \Slim\Views\TwigExtension(
        $container->router,
        $container->request->getUri()
    ));

    $view->getEnvironment()->addGlobal('userTimezone', $_SESSION['user.timezone']);
    
    $view->getEnvironment()->addGlobal('flash', $container->flash);

    return $view;
};

$container['notFoundHandler'] = function ($c) {
    return function ($request, $response) use ($c) {
        return $c['response']
            ->withStatus(404)
            ->withHeader('Content-Type', 'text/html')
            ->withRedirect($c->router->pathFor('notFound'));
    };
};

$container['validator'] = function ($container) {
    return new App\Validation\Validator;
};

require_once 'Controllers.php';

$container['csrf'] = function ($container) {
    return new \Slim\Csrf\Guard;
};

$app->add(new \App\Middleware\ValidationErrorsMiddleware($container));
$app->add(new \App\Middleware\OldInputMiddleware($container));
$app->add(new \App\Middleware\CsrfViewMiddleware($container));

//$app->add($container->csrf);


v::with('App\\Validation\\Rules\\');

require __DIR__ . '/../app/routes.php';

