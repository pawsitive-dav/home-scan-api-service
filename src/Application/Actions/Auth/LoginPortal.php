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

        if ((int)$usernameVerifyResult["approval"] === 0) {
            return $this->respondWithData("Your account is not approved yet!", 401);
        }

        if ($usernameVerifyResult["account_status"] === "suspended") {
            return $this->respondWithData("Forbidden", 403);
        }

        date_default_timezone_set("Asia/Bangkok");
        $dateNow = date('Y-m-d H:i:s');

        $pdo = $this->pdoConnect($_ENV['DB_PORTAL']);
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

        $this->checkResetCode($pdo, $usernameVerifyResult["account_id"]);

        $refreshToken = $this->createRefreshToken($usernameVerifyResult["account_id"]);

        return $this->respondWithData([
            "refreshToken" => $refreshToken
        ]);
    }

    private function checkResetCode($pdo, $account_id)
    {
        $dbTable = 'account';

        // Check if reset_password_code is not null
        $sqlCheckNull = "SELECT reset_password_code FROM " . $dbTable . " WHERE account_id = :account_id";
        $stmtCheckNull = $pdo->prepare($sqlCheckNull);
        $stmtCheckNull->bindValue(':account_id', $account_id);
        $stmtCheckNull->execute();

        $resetPasswordCode = $stmtCheckNull->fetchColumn();

        if ($resetPasswordCode !== null) {
            // If reset_password_code is not null, update it to null
            $sqlQuery = "UPDATE " . $dbTable . "
                     SET
                         reset_password_code = NULL
                     WHERE 
                         account_id = :account_id";

            $stmt = $pdo->prepare($sqlQuery);
            $stmt->bindValue(':account_id', $account_id);
            $stmt->execute();
        }
    }
}
