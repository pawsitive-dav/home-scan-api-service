<?php

declare(strict_types=1);

namespace App\Application\Actions\Project;

use Psr\Http\Message\ResponseInterface as Response;

class InspcetionDeleteImage extends MainAction
{
    protected function action(): Response
    {
        $input = $this->getFormData();
        $allKeys = ["project_id", "inspection_id", "image_id", "image_path"];
        $requiredKeys = ["project_id", "inspection_id", "image_id", "image_path"];

        if (!$this->validateInputBody($input, $allKeys, $requiredKeys)) {
            return $this->respondWithData("Bad Request", 400);
        }

        $runDeleteImage = $this->deleteImage($input['image_path']);
        if (!$runDeleteImage) {
            return $this->respondWithData("Delete Image Fail", 404);
        }

        $pdo = $this->pdoConnect($_ENV['DB_PORTAL']);

        $sqlQuery = "DELETE FROM inspection_storage 
                    WHERE project_id = :project_id 
                    AND inspection_id = :inspection_id 
                    AND image_id = :image_id";

        $stmt = $pdo->prepare($sqlQuery);
        $stmt->bindValue(':project_id', $input['project_id']);
        $stmt->bindValue(':inspection_id', $input['inspection_id']);
        $stmt->bindValue(':image_id', $input['image_id']);
        $stmt->execute();

        if ($stmt->rowCount() == 0) {
            return $this->respondWithData("Fail", 404);
        } else {
            return $this->respondWithData("Success");
        }
    }
}
