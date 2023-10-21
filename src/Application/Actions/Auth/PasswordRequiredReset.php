<?php

declare(strict_types=1);

namespace App\Application\Actions\Auth;

use Psr\Http\Message\ResponseInterface as Response;

class PasswordRequiredReset extends AuthAction
{
    protected function action(): Response
    {
        $input = $this->getFormData();
        $allKeys = ["username"];
        $requiredKeys = ["username"];

        if (!$this->validateInputBody($input, $allKeys, $requiredKeys)) {
            return $this->respondWithData("Bad Request", 400);
        }

        $verifyUsername = $this->verifyUsername($input['username']);
        if (!$verifyUsername || $verifyUsername['account_status'] !== "active") {
            return $this->respondWithData("Not found", 404);
        }

        $reset_password_code = $verifyUsername["reset_password_code"];
        if (!$reset_password_code) {
            $eightDigitCode = $this->generate8DigitCode();
            $updateCode = $this->updateResetCode($input['username'], $eightDigitCode);
            if (!$updateCode) {
                return $this->respondWithData("Update Fail", 404);
            }
        }

        return $this->respondWithData("OK");
    }

    private function generate8DigitCode()
    {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $code = '';
        for ($i = 0; $i < 8; $i++) {
            $index = rand(0, strlen($characters) - 1);
            $code .= $characters[$index];
        }
        return $code;
    }

    private function updateResetCode($username, $resetCode)
    {
        $pdo = $this->pdoConnect($_ENV['DB_PORTAL']);
        $dbTable = 'account';
        $sqlQuery = "UPDATE " . $dbTable . "
                    SET 
                        reset_password_code = :reset_password_code 
                    WHERE 
                        username = :username";

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
