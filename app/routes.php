<?php

declare(strict_types=1);

use Slim\App;
use App\Application\Middleware\AccessMiddleware as VerrifyAccessToken;
use App\Application\Middleware\RefreshMiddleware as VerifyRefreshToken;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

use App\Application\Actions\Auth\RegisterPortal;
use App\Application\Actions\Auth\RegisterExternal;

use App\Application\Actions\Auth\AccountApproval;
use App\Application\Actions\Auth\AccountSetActive;
use App\Application\Actions\Auth\AccountSetSuspend;
use App\Application\Actions\Auth\AccountDelete;
use App\Application\Actions\Auth\LoginPortal;
use App\Application\Actions\Auth\LoginExternal;
use App\Application\Actions\Auth\VerifyToken;
use App\Application\Actions\Auth\VerifyUsername;

use App\Application\Actions\Member\GetMemberInfo;
use App\Application\Actions\Member\GetMemberInfoBy;

return function (App $app) {
    $app->options('/{routes:.*}', function (Request $request, Response $response) {
        return $response;
    });

    $app->group('/v1', function (Group $group) {
        $group->group('/auth', function (Group $group) {
            $group->group('/register', function (Group $group) {
                $group->post('/portal', RegisterPortal::class);
                $group->post('/external', RegisterExternal::class);
            });
            $group->group('/account', function (Group $group) {
                $group->post('/approval', AccountApproval::class)->add(VerrifyAccessToken::class);
                $group->post('/set-active', AccountSetActive::class)->add(VerrifyAccessToken::class);
                $group->post('/set-suspend', AccountSetSuspend::class)->add(VerrifyAccessToken::class);
                $group->post('/delete', AccountDelete::class)->add(VerrifyAccessToken::class);
            });
            $group->group('/login', function (Group $group) {
                $group->post('/portal', LoginPortal::class);
                $group->post('/external', LoginExternal::class);
            });
            $group->group('/verify', function (Group $group) {
                $group->post('/token', VerifyToken::class)->add(VerifyRefreshToken::class);
                $group->post('/username', VerifyUsername::class);
            });
        });
        $group->group('/member', function (Group $group) {
            $group->get('/', GetMemberInfo::class)->add(VerrifyAccessToken::class);
            $group->get('/{id}', GetMemberInfoBy::class)->add(VerrifyAccessToken::class);
        });
    });
};
