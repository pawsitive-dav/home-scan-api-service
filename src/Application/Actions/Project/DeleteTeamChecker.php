<?php

declare(strict_types=1);

namespace App\Application\Actions\Project;

use Psr\Http\Message\ResponseInterface as Response;

class DeleteTeamChecker extends MainAction
{
    protected function action(): Response
    {
        $input = $this->getFormData();
        $allKeys = ["project_id", "account_id"];
        $requiredKeys = ["project_id", "account_id"];

        if (!$this->validateInputBody($input, $allKeys, $requiredKeys)) {
            return $this->respondWithData("Bad Request", 400);
        }

        $pdo = $this->pdoConnect($_ENV['DB_PORTAL']);

        $sqlQuery = "DELETE FROM project_teams_checker 
                    WHERE project_id = :project_id 
                    AND checker_team = :checker_team";

        $stmt = $pdo->prepare($sqlQuery);
        $stmt->bindValue(':project_id', $input['project_id']);
        $stmt->bindValue(':checker_team', $input['account_id']);
        $stmt->execute();

        if ($stmt->rowCount() == 0) {
            return $this->respondWithData("Delete Fail", 404);
        } else {
            return $this->respondWithData("Delete Success");
        }
    }
}
