<?php

declare(strict_types=1);

namespace App\Application\Actions\Auth;

use Psr\Http\Message\ResponseInterface as Response;

class LoginPortal extends AuthAction
{
    protected function action(): Response
    {
        $input = $this->getFormData();
        $allKeys = ["username", "password"];
        $requiredKeys = ["username", "password"];

        if (!$this->validateInputBody($input, $allKeys, $requiredKeys)) {
            return $this->respondWithData("Bad Request", 400);
        }

        $usernameVerifyResult = $this->verifyUsername($input['username']);

        if (!$usernameVerifyResult) {
            return $this->respondWithData("Username or Password is incorrect!", 404);
        }

        if (!$this->verifyPassword($input['password'], $usernameVerifyResult["password"])) {
            return $this->respondWithData("Username or Password is incorrect!", 404);
        }

        if ($usernameVerifyResult["approval"] === 0) {
            return $this->respondWithData("Your account is not approved yet!", 401);
        }

        if ($usernameVerifyResult["account_status"] === "suspended") {
            return $this->respondWithData("Your account is suspended.", 401);
        }

        date_default_timezone_set("Asia/Bangkok");
        $dateNow = date('Y-m-d H:i:s');

        $pdo = $this->pdoConnect($_ENV['DB_MEMBER']);
        $dbTable = 'account';
        $sqlQuery = "UPDATE " . $dbTable . "
                        SET
                            last_login = :last_login 
                        WHERE 
                            account_id = :account_id";

        $stmt = $pdo->prepare($sqlQuery);
        $stmt->bindValue(':last_login', $dateNow);
        $stmt->bindValue(':account_id', $usernameVerifyResult["account_id"]);
        $stmt->execute();

        $refreshToken = $this->createRefreshToken($usernameVerifyResult["account_id"]);

        return $this->respondWithData([
            "refreshToken" => $refreshToken
        ]);
    }
}
