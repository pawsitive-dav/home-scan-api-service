<?php

declare(strict_types=1);

namespace App\Application\Actions\Project;

use Psr\Http\Message\ResponseInterface as Response;

class InspcetionDeleteStorageGroup extends MainAction
{
    protected function action(): Response
    {
        $input = $this->getFormData();
        $allKeys = ["project_id", "inspection_id", "image_id_group"];
        $requiredKeys = ["project_id", "inspection_id", "image_id_group"];

        if (!$this->validateInputBody($input, $allKeys, $requiredKeys)) {
            return $this->respondWithData("Bad Request", 400);
        }

        $allList = explode(",", $input['image_id_group']);

        $pdo = $this->pdoConnect($_ENV['DB_PORTAL']);

        $imagePaths = [];

        foreach ($allList as $data) {
            $sqlQuery = "SELECT image_path FROM inspection_storage WHERE image_id = :image_id";
            $stmt = $pdo->prepare($sqlQuery);
            $stmt->bindParam(':image_id', $data);
            $stmt->execute();

            $result = $stmt->fetch();

            if ($result) {
                $imagePaths[] = $result['image_path'];
            }
        }

        if (empty($imagePaths)) {
            return $this->respondWithData("No data found", 404);
        }

        foreach ($imagePaths as $data) {
            $runDeleteImage = $this->deleteImage($data);
            if (!$runDeleteImage) {
                return $this->respondWithData("Delete Image Fail", 404, "Error deleting image: {$data}");
            }

            $sqlQuery = "DELETE FROM inspection_storage WHERE image_path = :image_path";
            $stmt = $pdo->prepare($sqlQuery);
            $stmt->bindValue(':image_path', $data);
            $deleteResult = $stmt->execute();

            if (empty($deleteResult)) {
                return $this->respondWithData("Delete Storage Fail", 404, "Error deleting storage: {$data}");
            }
        }

        return $this->respondWithData(count($imagePaths));
    }
}
