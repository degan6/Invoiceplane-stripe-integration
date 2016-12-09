<?php

use App\Middleware\AuthMiddleware;
use App\Middleware\GuestMiddleware;
use App\Middleware\ConfirmEmailMiddleware;

$app->group('', function () use ($container) {

    $this->get('/invoice/{invoiceURLKey}[/]', 'HomeController:index')->setName('home');

    $this->post('/stripetoken/{invoiceURLKey}[/]', 'HomeController:stripetoken')->setName('stripetoken');

    $this->get('/paid/{invoiceURLKey}[/]', 'HomeController:paid')->setName('paid');

    $this->get('/invoice/', 'HomeController:noURLKey')->setName('notFound');

});


//});
