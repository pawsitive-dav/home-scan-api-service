<?php

declare(strict_types=1);

namespace App\Application\Actions\Auth;

use Psr\Http\Message\ResponseInterface as Response;

class AccountApproval extends AuthAction
{
    protected function action(): Response
    {
        $input = $this->getFormData();
        $allKeys = ["account_id"];
        $requiredKeys = ["account_id"];

        if (!$this->validateInputBody($input, $allKeys, $requiredKeys)) {
            return $this->respondWithData("Bad Request", 400);
        }

        $pdo = $this->pdoConnect($_ENV['DB_MEMBER']);
        $dbTable = 'account';
        $sqlQuery = "UPDATE " . $dbTable . "
                    SET 
                        approval = :approval, 
                        account_status = :account_status 
                    WHERE 
                        account_id = :account_id AND 
                        approval != 1";

        $stmt = $pdo->prepare($sqlQuery);
        $stmt->bindValue(':approval', 1);
        $stmt->bindValue(':account_status', 'active');
        $stmt->bindValue(':account_id', $input['account_id']);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            return $this->respondWithData("Account is approved.");
        } else {
            return $this->respondWithData("Failed", 400);
        }
    }
}
