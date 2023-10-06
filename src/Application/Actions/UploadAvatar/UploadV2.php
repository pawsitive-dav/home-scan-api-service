<?php

declare(strict_types=1);

namespace App\Application\Actions\UploadAvatar;

use Psr\Http\Message\ResponseInterface as Response;

class UploadV2 extends MainAction
{
    protected function action(): Response
    {
        $uploadedFiles = $this->getUploadedFiles();

        return $this->respondWithData($uploadedFiles);
    }

    private function updateAvatarPath($pdo, $account_id, $imageId, $oldAvatar)
    {
        $dbTable = 'member_info';
        $sqlQuery = "UPDATE " . $dbTable . "
                        SET 
                            avatar_path = :avatar_path
                        WHERE 
                            account_id = :account_id";

        $stmt = $pdo->prepare($sqlQuery);
        $stmt->bindValue(':account_id', $account_id);
        $stmt->bindValue(':avatar_path', $imageId);
        $stmt->execute();

        if ($oldAvatar) {
            $filename = $oldAvatar . '.jpeg';
            $deleteDirectory = '../resources/avatar/';
            $filePath = $deleteDirectory . $filename;
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }

        return $stmt->rowCount() > 0;
    }

    private function checkOldAvatar($pdo, $account_id)
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
