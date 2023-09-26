<?php

declare(strict_types=1);

namespace App\Application\Actions\Auth;

use Psr\Http\Message\ResponseInterface as Response;

class VerifyToken extends AuthAction
{
    protected function action(): Response
    {
        $tokenInfo = $this->request->getAttribute('tokenInfo');

        date_default_timezone_set("Asia/Bangkok");
        $expTimestamp = $tokenInfo->exp;

        $startDateTime = date('Y-m-d H:i:s', time());
        $endDateTime = date('Y-m-d H:i:s', $expTimestamp);

        $startTimestamp = strtotime($startDateTime);
        $endTimestamp = strtotime($endDateTime);
        $timeDifference = abs($endTimestamp - $startTimestamp);
        $hourDifference = floor($timeDifference / 3600);

        if ($hourDifference <= 3) {
            $refreshToken = $this->createRefreshToken($tokenInfo->data);
            $accessToken = $this->createAccessToken($tokenInfo->data);
            return $this->respondWithData([
                "refresh" => true,
                "refreshToken" => $refreshToken,
                "accessToken" => $accessToken
            ], 200);
        } else {
            $accessToken = $this->createAccessToken($tokenInfo->data);
            return $this->respondWithData([
                "refresh" => false,
                "refreshToken" => false,
                "accessToken" => $accessToken
            ], 200);
        }
    }
}
