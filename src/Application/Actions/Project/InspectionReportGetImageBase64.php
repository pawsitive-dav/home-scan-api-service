<?php

declare(strict_types=1);

namespace App\Application\Actions\Project;

use Psr\Http\Message\ResponseInterface as Response;

class InspectionReportGetImageBase64 extends MainAction
{
    protected function action(): Response
    {
        $input = $this->getFormData();
        $allKeys = ["image_path"];
        $requiredKeys = ["image_path"];

        if (!$this->validateInputBody($input, $allKeys, $requiredKeys)) {
            return $this->respondWithData("Bad Request", 400);
        }

        $cutJpeg = str_replace('.jpeg', '', $input['image_path']);
        $oldAvatarParts = explode('/', $cutJpeg);
        $imageId = $oldAvatarParts[5];
        $storage = $oldAvatarParts[4];

        $urlEndpoint = $_ENV['STORAGE_ENDPOINT'] . '/get.php';

        $ch = curl_init($urlEndpoint);
        $payload = json_encode([
            'image_id' => $imageId,
            'storage' => $storage
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

        $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $resultData['image_64']));
        $imageSizeInfo = getimagesizefromstring($imageData);
        if ($imageSizeInfo !== false) {
            $width = $imageSizeInfo[0];
            $height = $imageSizeInfo[1];
        }

        $jsonData = [
            "image" => $resultData['image_64'],
            "width" => $width,
            "height" => $height
        ];

        return $this->respondWithData($jsonData);
    }
}
