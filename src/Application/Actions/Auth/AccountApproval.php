<?php

declare(strict_types=1);

namespace App\Application\Actions\Auth;

use Psr\Http\Message\ResponseInterface as Response;

class AccountApproval extends AuthAction
{
    protected function action(): Response
    {
        // Validate input
        $input = $this->getFormData();
        $allKeys = ["account_id", "app_role"];
        $requiredKeys = ["account_id", "app_role"];

        if (!$this->validateInputBody($input, $allKeys, $requiredKeys)) {
            return $this->respondWithData("Bad Request", 400);
        }

        $pdo = $this->pdoConnect($_ENV['DB_MEMBER']);

        $updateQuery = "
            UPDATE account a
            JOIN member_info m ON a.account_id = m.account_id
            SET 
                a.approval = 1,
                a.account_status = 'active',
                m.member_role = :member_role
            WHERE 
                a.account_id = :account_id AND 
                a.approval != 1
        ";

        $stmt = $pdo->prepare($updateQuery);
        $stmt->bindValue(':member_role', $input['app_role']);
        $stmt->bindValue(':account_id', $input['account_id']);
        $stmt->execute();

        if ($stmt->rowCount() == 0) {
            return $this->respondWithData("Failed", 400);
        }

        return $this->respondWithData("Account is approved.");
    }
}
