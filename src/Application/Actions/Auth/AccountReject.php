<?php

declare(strict_types=1);

namespace App\Application\Actions\Auth;

use Psr\Http\Message\ResponseInterface as Response;

class AccountReject extends AuthAction
{
    protected function action(): Response
    {
        $input = $this->getFormData();
        $allKeys = ["account_id"];
        $requiredKeys = ["account_id"];

        if (!$this->validateInputBody($input, $allKeys, $requiredKeys)) {
            return $this->respondWithData("Bad Request", 400);
        }

        $account_id = $input['account_id'];
        $pdo = $this->pdoConnect($_ENV['DB_PORTAL']);

        $accountDeleteQuery = "DELETE FROM account WHERE account_id = :account_id";
        $accountDeleteStmt = $pdo->prepare($accountDeleteQuery);
        $accountDeleteStmt->bindValue(':account_id', $account_id);
        $accountDeleteStmt->execute();

        $memberInfoDeleteQuery = "DELETE FROM member_info WHERE account_id = :account_id";
        $memberInfoDeleteStmt = $pdo->prepare($memberInfoDeleteQuery);
        $memberInfoDeleteStmt->bindValue(':account_id', $account_id);
        $memberInfoDeleteStmt->execute();

        if ($accountDeleteStmt->rowCount() > 0 && $memberInfoDeleteStmt->rowCount() > 0) {
            return $this->respondWithData("Account Rejected.");
        } else {
            return $this->respondWithData("Account not found or delete failed.", 400);
        }
    }
}
