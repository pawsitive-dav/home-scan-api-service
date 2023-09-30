<?php

declare(strict_types=1);

namespace App\Application\Actions\Auth;

use Psr\Http\Message\ResponseInterface as Response;

class PasswordReset extends AuthAction
{
    protected function action(): Response
    {
        $input = $this->getFormData();
        $allKeys = ["username", "reset_code", "new_password"];
        $requiredKeys = ["username", "reset_code", "new_password"];

        if (!$this->validateInputBody($input, $allKeys, $requiredKeys)) {
            return $this->respondWithData("Bad Request", 400);
        }

        $verifyUsername = $this->verifyUsername($input['username']);
        if (!$verifyUsername || $verifyUsername['account_status'] !== "active") {
            return $this->respondWithData("Not found", 404);
        }

        $checkResetCode = $this->checkResetCode($input['username'], $input['reset_code']);
        if (!$checkResetCode) {
            return $this->respondWithData("Not found", 404);
        }

        $updatePassword = $this->updateNewPassword($input['username'], $input['new_password']);
        if (!$updatePassword) {
            return $this->respondWithData("Update Fail", 404);
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

        return $stmt->rowCount() > 0;
    }

    private function updateNewPassword($username, $new_password)
    {
        $pdo = $this->pdoConnect($_ENV['DB_MEMBER']);
        $passwordHashed = $this->hashPassword($new_password);

        $dbTable = 'account';
        $sqlQuery = "UPDATE " . $dbTable . "
                    SET 
                        password = :new_password, 
                        reset_password_code = :reset_password_code
                    WHERE 
                        username = :username";

        $stmt = $pdo->prepare($sqlQuery);
        $stmt->bindValue(':username', $username);
        $stmt->bindValue(':new_password', $passwordHashed);
        $stmt->bindValue(':reset_password_code', null);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }
}
