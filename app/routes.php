<?php

declare(strict_types=1);

use Slim\App;
use App\Application\Middleware\AccessMiddleware as VerrifyAccessToken;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;


use App\Application\Actions\ImageAvatar\Upload as AvatartUpload;


return function (App $app) {
    $app->options('/{routes:.*}', function (Request $request, Response $response) {
        return $response;
    });

    $app->group('/v1', function (Group $group) {
        $group->group('/image', function (Group $group) {
            $group->group('/avatar', function (Group $group) {
                $group->post('/upload', AvatartUpload::class);
                // $group->post('/upload', AvatartUpload::class)->add(VerrifyAccessToken::class);
            });
        });
    });
};
