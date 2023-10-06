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
            return $this->respondWithData("Bad Request: Missing or invalid image data.", 400);
        }

        $inputImage = $input['image'];
        $imageDecode = base64_decode($inputImage);

        $pdo = $this->pdoConnect($_ENV['DB_MEMBER']);
        $account_id = $this->request->getAttribute('tokenInfo')->data;

        $oldAvatar = $this->checkOldAvatar($pdo, $account_id);

        if ($imageDecode === false) {
            return $this->respondWithData("Bad Request: Invalid base64-encoded image.", 400);
        }

        $image_parts = explode(";base64,", $imageDecode);
        if (count($image_parts) !== 2) {
            return $this->respondWithData("Bad Request: Invalid base64-encoded image format.", 400);
        }

        $image_base64 = base64_decode($image_parts[1]);

        if ($image_base64 === false) {
            return $this->respondWithData("Bad Request: Failed to decode base64 image.", 400);
        }

        $imageId = uniqid();
        $filename = $imageId . '.jpeg';
        $uploadDirectory = '../resources/avatar/';
        $filePath = $uploadDirectory . $filename;

        $fileSaved = file_put_contents($filePath, $image_base64);

        if ($fileSaved === false) {
            return $this->respondWithData("Internal Server Error: Failed to save the image.", 500);
        }

        $updateAvatar = $this->updateAvatarPath($pdo, $account_id, $imageId, $oldAvatar);
        if (!$updateAvatar) {
            return $this->respondWithData("Update Fail", 404);
        }

        return $this->respondWithData("Upload avatar successfully");
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
