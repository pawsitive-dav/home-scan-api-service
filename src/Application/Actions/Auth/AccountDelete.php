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

        $oldAvatarUrl = $this->checkAvatar($pdo, $account_id);
        if ($oldAvatarUrl) {
            $cutJpeg = str_replace('.jpeg', '', $oldAvatarUrl);
            $oldAvatarParts = explode('/', $cutJpeg);
            $imageIdDelete = $oldAvatarParts[5];
            $storageDelete = $oldAvatarParts[4];

            $deleteImageEndpoint = $_ENV['STORAGE_ENDPOINT'] . '/delete.php';
            $setUrl = $deleteImageEndpoint . "?storage=" . $storageDelete . "&image_id=" . $imageIdDelete;

            $chDelete = curl_init($setUrl);
            curl_setopt($chDelete, CURLOPT_CUSTOMREQUEST, "DELETE");
            curl_setopt($chDelete, CURLOPT_RETURNTRANSFER, true);
            $deleteResponse = curl_exec($chDelete);
            curl_close($chDelete);

            if (!$deleteResponse) {
                return $this->respondWithData("Delete Fail", 404);
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

    private function checkAvatar($pdo, $account_id)
    {
        $dbTable = 'member_info';
        $sqlQuery = "SELECT * FROM " . $dbTable . " WHERE account_id = :account_id";
        $stmt = $pdo->prepare($sqlQuery);
        $stmt->bindValue(':account_id', $account_id);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            $allData = $stmt->fetchAll();
            return $allData[0]['avatar_path'];
        } else {
            return false;
        }
    }
}
