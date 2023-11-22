<?php

declare(strict_types=1);

namespace App\Application\Actions\Project;

use Psr\Http\Message\ResponseInterface as Response;

class EditProjectNote extends MainAction
{
    protected function action(): Response
    {
        $input = $this->getFormData();
        $allKeys = ["project_id", "project_note"];
        $requiredKeys = ["project_id"];

        if (!$this->validateInputBody($input, $allKeys, $requiredKeys)) {
            return $this->respondWithData("Bad Request", 400);
        }

        $projectNote = empty($input['project_note']) ? null : $input['project_note'];

        $pdo = $this->pdoConnect($_ENV['DB_PORTAL']);

        $sqlQuery = "UPDATE project_detail
                     SET 
                        project_note = :project_note
                     WHERE 
                        project_id = :project_id";

        $stmt = $pdo->prepare($sqlQuery);
        $stmt->bindValue(':project_id', $input['project_id']);
        $stmt->bindValue(':project_note', $projectNote);

        if (!$stmt->execute()) {
            return $this->respondWithData("Failed to edit", 404);
        }

        return $this->respondWithData("Edit Successfully.", 200);
    }
}
