<?php

declare(strict_types=1);

namespace App\Application\Actions\UploadAvatar;

use Psr\Http\Message\ResponseInterface as Response;

class Upload extends MainAction
{
    protected function action(): Response
    {
        $input = $this->getFormData();
        $allKeys = ["image"];
        $requiredKeys = ["image"];

        if (!$this->validateInputBody($input, $allKeys, $requiredKeys)) {
            return $this->respondWithData("Bad Request", 400);
        }

        $pdo = $this->pdoConnect($_ENV['DB_PORTAL']);
        $account_id = $this->request->getAttribute('tokenInfo')->data;
        $oldAvatarUrl = $this->checkOldAvatar($pdo, $account_id);
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

        $urlEndpoint = $_ENV['STORAGE_ENDPOINT'] . '/upload.php';
        $ch = curl_init($urlEndpoint);
        $payload = json_encode([
            'image' => $input['image'],
            'storage' => 'avatars'
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type:application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        curl_close($ch);

        if (!$result) {
            return $this->respondWithData("Upload Fail", 404);
        }

        $resultData = json_decode($result, true);
        $imagePath = str_replace('\/', '/', $resultData['file_path']);

        $updateAvatar = $this->updateAvatarUrl($pdo, $account_id, $imagePath);
        if (!$updateAvatar) {
            return $this->respondWithData("Update Fail", 404);
        } else {
            return $this->respondWithData("Upload Avatar Successfuly");
        }
    }

    private function updateAvatarUrl($pdo, $account_id, $imageUrl)
    {
        $dbTable = 'member_info';
        $sqlQuery = "UPDATE " . $dbTable . "
                        SET 
                            avatar_path = :avatar_path
                        WHERE 
                            account_id = :account_id";

        $stmt = $pdo->prepare($sqlQuery);
        $stmt->bindValue(':account_id', $account_id);
        $stmt->bindValue(':avatar_path', $imageUrl);
        $stmt->execute();

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
