<?php


$app->get('/invoice/{invoiceURLKey}[/]', 'HomeController:index')->setName('home');

$app->post('/stripetoken/{invoiceURLKey}[/]', 'HomeController:stripetoken')->setName('stripetoken');

$app->get('/paid/{invoiceURLKey}[/]', 'HomeController:paid')->setName('paid');

$app->get('/invoice/', 'HomeController:noURLKey')->setName('notFound');
