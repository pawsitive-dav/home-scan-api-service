<?php

declare(strict_types=1);

namespace App\Application\Actions\Project;

use Psr\Http\Message\ResponseInterface as Response;

class EditTypeUsableArea extends MainAction
{
    protected function action(): Response
    {
        $input = $this->getFormData();
        $allKeys = ["project_id", "type_usable_area"];
        $requiredKeys = ["project_id"];

        if (!$this->validateInputBody($input, $allKeys, $requiredKeys)) {
            return $this->respondWithData("Bad Request", 400);
        }

        $pdo = $this->pdoConnect($_ENV['DB_PORTAL']);

        $sqlQuery = "UPDATE project_type_detail
                     SET 
                        type_usable_area = :type_usable_area
                     WHERE 
                        project_id = :project_id";

        $stmt = $pdo->prepare($sqlQuery);
        $stmt->bindValue(':project_id', $input['project_id']);
        $stmt->bindValue(':type_usable_area', $input['type_usable_area']);

        if (!$stmt->execute()) {
            return $this->respondWithData("Failed to edit", 404);
        }

        return $this->respondWithData("Edit Successfully.", 200);
    }
}
