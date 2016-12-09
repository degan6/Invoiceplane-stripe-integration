<?php
/**
 * Created by PhpStorm.
 * User: de
 * Date: 9/24/2016
 * Time: 10:03 PM
 */

$container['HomeController'] = function ($container) {
    return new \App\Controllers\HomeController($container);
};

$container['WebhookController'] = function ($container) {
    return new \App\Controllers\WebhookController($container);
};