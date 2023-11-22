<?php

declare(strict_types=1);

namespace App\Application\Actions\Project;

use Psr\Http\Message\ResponseInterface as Response;

class InspcetionEditImageName extends MainAction
{
    protected function action(): Response
    {
        $input = $this->getFormData();

        $allKeys = ["project_id", "inspection_id", "image_id", "image_name"];
        $requiredKeys = ["project_id", "inspection_id", "image_id"];

        if (!$this->validateInputBody($input, $allKeys, $requiredKeys)) {
            return $this->respondWithData("Bad Request", 400);
        }

        $pdo = $this->pdoConnect($_ENV['DB_PORTAL']);

        $sqlQuery = "UPDATE inspection_storage
                    SET
                        image_name = :image_name 
                    WHERE
                        project_id = :project_id
                        AND inspection_id = :inspection_id
                        AND image_id = :image_id";

        $stmt = $pdo->prepare($sqlQuery);
        $stmt->bindValue(':image_name', $input['image_name']);
        $stmt->bindValue(':project_id', $input['project_id']);
        $stmt->bindValue(':inspection_id', $input['inspection_id']);
        $stmt->bindValue(':image_id', $input['image_id']);

        $result = $stmt->execute();

        if (!$result) {
            return $this->respondWithData("Fail", 404);
        } else {
            return $this->respondWithData("Success");
        }
    }
}
