<?php

declare(strict_types=1);

namespace App\Application\Actions\Auth;

use Psr\Http\Message\ResponseInterface as Response;

class PasswordVerifyCode extends AuthAction
{
    protected function action(): Response
    {
        $input = $this->getFormData();
        $allKeys = ["username", "reset_code"];
        $requiredKeys = ["username", "reset_code"];

        if (!$this->validateInputBody($input, $allKeys, $requiredKeys)) {
            return $this->respondWithData("Bad Request", 400);
        }

        $verifyUsername = $this->verifyUsername($input['username']);
        if (!$verifyUsername || $verifyUsername['account_status'] !== "active") {
            return $this->respondWithData("Not found", 404);
        }

        $result = $this->checkResetCode($input['username'], $input['reset_code']);
        if (!$result) {
            return $this->respondWithData("Not found", 404);
        }

        return $this->respondWithData("OK");
    }

    private function checkResetCode($username, $resetCode)
    {
        $pdo = $this->pdoConnect($_ENV['DB_MEMBER']);
        $dbTable = 'account';
        $sqlQuery = "SELECT * FROM " . $dbTable . " WHERE username = :username AND reset_password_code = :reset_password_code";

        $stmt = $pdo->prepare($sqlQuery);
        $stmt->bindValue(':username', $username);
        $stmt->bindValue(':reset_password_code', $resetCode);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            return true;
        } else {
            return false;
        }
    }
}
