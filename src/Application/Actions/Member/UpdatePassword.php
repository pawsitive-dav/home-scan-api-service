<?php

declare(strict_types=1);

namespace App\Application\Actions\Member;

use Psr\Http\Message\ResponseInterface as Response;

class UpdatePassword extends MainAction
{
    protected function action(): Response
    {
        $input = $this->getFormData();
        $allKeys = ["new_password"];
        $requiredKeys = ["new_password"];

        if (!$this->validateInputBody($input, $allKeys, $requiredKeys)) {
            return $this->respondWithData("Bad Request", 400);
        }

        $account_id = $this->request->getAttribute('tokenInfo')->data;
        $passwordHashed = $this->hashPassword($input['new_password']);

        $pdo = $this->pdoConnect($_ENV['DB_MEMBER']);
        $dbTable = 'account';
        $sqlQuery = "UPDATE {$dbTable}
                        SET
                            password = :password 
                        WHERE 
                            account_id = :account_id";

        $stmt = $pdo->prepare($sqlQuery);
        $stmt->bindValue(':password', $passwordHashed);
        $stmt->bindValue(':account_id', $account_id);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            return $this->respondWithData("Password reset successfully");
        } else {
            return $this->respondWithData("Password reset failed", 404);
        }
    }
}
