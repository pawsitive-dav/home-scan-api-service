<?php

declare(strict_types=1);

namespace App\Application\Actions\Project;

use Psr\Http\Message\ResponseInterface as Response;

class InspcetionGetSystemDeflectDelete extends MainAction
{
    protected function action(): Response
    {
        $input = $this->getFormData();
        $allKeys = ["project_id", "inspection_id", "system_id", "image_id"];
        $requiredKeys = ["project_id", "inspection_id", "system_id", "image_id"];

        if (!$this->validateInputBody($input, $allKeys, $requiredKeys)) {
            return $this->respondWithData("Bad Request", 400);
        }

        $pdo = $this->pdoConnect($_ENV['DB_PORTAL']);

        $sqlQuery = "DELETE FROM inspection_system_deflect 
                    WHERE project_id = :project_id 
                    AND inspection_id = :inspection_id 
                    AND system_id = :system_id 
                    AND image_id = :image_id";

        $stmt = $pdo->prepare($sqlQuery);
        $stmt->bindValue(':project_id', $input['project_id']);
        $stmt->bindValue(':inspection_id', $input['inspection_id']);
        $stmt->bindValue(':system_id', $input['system_id']);
        $stmt->bindValue(':image_id', $input['image_id']);
        $success = $stmt->execute();

        if (!$success) {
            return $this->respondWithData("Failed to delete data", 404);
        }

        return $this->respondWithData('Delete deflect successfuly');
    }
}
