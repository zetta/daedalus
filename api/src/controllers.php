<?php

/**
 * Definition of routes
 */

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

$app->post('/route', function (Request $request) use ($app) {
    if ($app['route.input.validator']->isValid($request->getContent())) {
        $token = $app['route.service']->storeNew($request->getContent());

        $app['background.processor']->processRoute($token);

        return new JsonResponse(['token' => $token]);
    } else {
        return new JsonResponse(['error' => 'Invalid Input'], 400);
    }
});

$app->get('/route/{token}', function($token) use ($app) {
    $route = $app['route.service']->findRouteByToken($token);
    $route = $app['route.service']->prepareRoute($route);
    return new JsonResponse($route);
});

$app->error(function (\Exception $e, Request $request, $code) {
    return new JsonResponse(['error' => $e->getMessage()], $code);
});
