<?php

declare(strict_types=1);

namespace App\Application\Actions\Auth;

use Psr\Http\Message\ResponseInterface as Response;

class AccountDelete extends AuthAction
{
    protected function action(): Response
    {
        $account_id = $this->request->getAttribute('tokenInfo')->data;

        $pdo = $this->pdoConnect($_ENV['DB_MEMBER']);
        $sqlQuery = "SELECT * FROM member_info WHERE account_id = :account_id ";
        $stmt = $pdo->prepare($sqlQuery);
        $stmt->bindValue(":account_id", $account_id);
        $stmt->execute();

        if ($stmt->rowCount() == 0) {
            return $this->respondWithData("Failed", 400);
        }

        $avatarPath = $stmt->fetchAll()[0]['avatar_path'];
        if ($avatarPath) {
            $filename = $avatarPath . '.jpeg';
            $deleteDirectory = '../resources/avatar/';
            $filePath = $deleteDirectory . $filename;
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }

        $accountDeleteQuery = "DELETE FROM account WHERE account_id = :account_id";
        $accountDeleteStmt = $pdo->prepare($accountDeleteQuery);
        $accountDeleteStmt->bindValue(':account_id', $account_id);
        $accountDeleteStmt->execute();

        $memberInfoDeleteQuery = "DELETE FROM member_info WHERE account_id = :account_id";
        $memberInfoDeleteStmt = $pdo->prepare($memberInfoDeleteQuery);
        $memberInfoDeleteStmt->bindValue(':account_id', $account_id);
        $memberInfoDeleteStmt->execute();

        if ($accountDeleteStmt->rowCount() > 0 && $memberInfoDeleteStmt->rowCount() > 0) {
            return $this->respondWithData("Account deleted successfully.");
        } else {
            return $this->respondWithData("Account not found or delete failed.", 400);
        }
    }
}
