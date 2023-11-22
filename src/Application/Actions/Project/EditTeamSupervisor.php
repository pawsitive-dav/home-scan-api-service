<?php

declare(strict_types=1);

namespace App\Application\Actions\Project;

use Psr\Http\Message\ResponseInterface as Response;

class EditTeamSupervisor extends MainAction
{
    protected function action(): Response
    {
        $input = $this->getFormData();
        $allKeys = ["project_id", "checker_supervisor"];
        $requiredKeys = ["project_id", "checker_supervisor"];

        if (!$this->validateInputBody($input, $allKeys, $requiredKeys)) {
            return $this->respondWithData("Bad Request", 400);
        }

        $pdo = $this->pdoConnect($_ENV['DB_PORTAL']);

        $sqlQuery = "UPDATE project_teams
                     SET 
                        checker_supervisor = :checker_supervisor
                     WHERE 
                        project_id = :project_id";

        $stmt = $pdo->prepare($sqlQuery);
        $stmt->bindValue(':project_id', $input['project_id']);
        $stmt->bindValue(':checker_supervisor', $input['checker_supervisor']);

        if (!$stmt->execute()) {
            return $this->respondWithData("Failed to edit", 404);
        }

        return $this->respondWithData("Edit Successfully.", 200);
    }
}
