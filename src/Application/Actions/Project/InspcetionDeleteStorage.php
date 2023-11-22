<?php

declare(strict_types=1);

namespace App\Application\Actions\Project;

use Psr\Http\Message\ResponseInterface as Response;

class InspcetionDeleteStorage extends MainAction
{
    protected function action(): Response
    {
        $input = $this->getFormData();
        $allKeys = ["project_id", "inspection_id"];
        $requiredKeys = ["project_id", "inspection_id"];

        if (!$this->validateInputBody($input, $allKeys, $requiredKeys)) {
            return $this->respondWithData("Bad Request", 400);
        }

        $pdo = $this->pdoConnect($_ENV['DB_PORTAL']);

        $sqlQuery = "SELECT * FROM inspection_storage 
                    WHERE project_id = :project_id AND inspection_id = :inspection_id";

        $stmt = $pdo->prepare($sqlQuery);
        $stmt->bindValue(':project_id', $input['project_id']);
        $stmt->bindValue(':inspection_id', $input['inspection_id']);
        $stmt->execute();

        $allData = $stmt->fetchAll();

        foreach ($allData as $data) {
            $runDeleteImage = $this->deleteImage($data['image_path']);
            if (!$runDeleteImage) return $this->respondWithData("Delete Image Fail", 404);
        }

        $sqlQuery = "DELETE FROM inspection_storage 
                    WHERE project_id = :project_id 
                    AND inspection_id = :inspection_id";

        $stmt = $pdo->prepare($sqlQuery);
        $stmt->bindValue(':project_id', $input['project_id']);
        $stmt->bindValue(':inspection_id', $input['inspection_id']);
        $deleteResult = $stmt->execute();
        if (empty($deleteResult)) return $this->respondWithData("Delete Storage Fail", 404);

        return $this->respondWithData(count($allData));
    }
}
