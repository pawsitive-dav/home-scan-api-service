<?php

declare(strict_types=1);

namespace App\Application\Actions\Project;

use Psr\Http\Message\ResponseInterface as Response;

class AddTeamChecker extends MainAction
{
    protected function action(): Response
    {
        $input = $this->getFormData();
        $allKeys = ["project_id", "checker_team"];
        $requiredKeys = ["project_id", "checker_team"];

        if (!$this->validateInputBody($input, $allKeys, $requiredKeys)) {
            return $this->respondWithData("Bad Request", 400);
        }

        $pdo = $this->pdoConnect($_ENV['DB_PORTAL']);

        $sqlQuery = "INSERT INTO project_teams_checker
                            SET
                                project_id = :project_id, 
                                checker_team = :checker_team";

        $stmt = $pdo->prepare($sqlQuery);
        $stmt->bindValue(':project_id', $input['project_id']);
        $stmt->bindValue(':checker_team', $input['checker_team']);
        $result = $stmt->execute();

        if (!$result) {
            return $this->respondWithData("Fail", 404);
        } else {
            return $this->respondWithData("Success");
        }
    }
}
