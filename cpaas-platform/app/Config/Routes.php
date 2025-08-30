<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->group('api/rest', ['filter' => 'apikey'], static function (RouteCollection $routes) {
    $routes->post('otp/generate', 'Api\OtpController::generate');
    $routes->post('otp/check', 'Api\OtpController::check');
    $routes->post('email/send', 'Api\EmailController::send');
});
