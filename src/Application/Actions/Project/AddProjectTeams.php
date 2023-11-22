<?php

declare(strict_types=1);

namespace App\Application\Actions\Project;

use Psr\Http\Message\ResponseInterface as Response;

class AddProjectTeams extends MainAction
{
    protected function action(): Response
    {
        $input = $this->getFormData();
        $allKeys = ["project_id", "account_id", "team_type"];
        $requiredKeys = ["project_id", "account_id", "team_type"];

        if (!$this->validateInputBody($input, $allKeys, $requiredKeys)) {
            return $this->respondWithData("Bad Request", 400);
        }

        $pdo = $this->pdoConnect($_ENV['DB_PORTAL']);

        // Default value for $resultUpdateStatus
        $resultUpdateStatus = false;

        if ($input['team_type'] === 'supervisor') {

            $sqlQuery = "UPDATE project_teams
                        SET checker_supervisor = :checker_supervisor
                        WHERE project_id = :project_id";

            $stmt = $pdo->prepare($sqlQuery);
            $stmt->bindValue(':checker_supervisor', $input['account_id']);
            $stmt->bindValue(':project_id', $input['project_id']);
            $result = $stmt->execute();

            // Update $resultUpdateStatus based on the outcome of the conditional block
            $resultUpdateStatus = $result;

            $sqlQueryInspection = "SELECT * FROM inspection_detail WHERE project_id = :project_id";
            $stmtInspection = $pdo->prepare($sqlQueryInspection);
            $stmtInspection->bindValue(':project_id', $input['project_id']);
            $stmtInspection->execute();
            $hasInspectionData = $stmtInspection->rowCount() > 0;

            if ($hasInspectionData) {
                date_default_timezone_set("Asia/Bangkok");
                $DATE_NOW = date('Y-m-d H:i:s');
                $sqlQueryUpdateStatus = "UPDATE project_detail
                            SET
                                project_status = 'in-progress',  
                                status_update_at = :status_update_at
                            WHERE
                                project_id = :project_id";

                $stmtUpdateStatus = $pdo->prepare($sqlQueryUpdateStatus);
                $stmtUpdateStatus->bindValue(':project_id', $input['project_id']);
                $stmtUpdateStatus->bindValue(':status_update_at', $DATE_NOW);
                $resultUpdateStatus = $stmtUpdateStatus->execute();

                // Update $resultUpdateStatus based on the outcome of the conditional block
                $resultUpdateStatus = $resultUpdateStatus && $resultUpdateStatus;
            }
        }

        if ($input['team_type'] === 'checker') {
            $sqlQuery = "INSERT INTO project_teams_checker
                        SET 
                            project_id = :project_id,
                            checker_team = :checker_team";

            $stmt = $pdo->prepare($sqlQuery);
            $stmt->bindValue(':checker_team', $input['account_id']);
            $stmt->bindValue(':project_id', $input['project_id']);
            $result = $stmt->execute();

            // Update $resultUpdateStatus based on the outcome of the conditional block
            $resultUpdateStatus = $resultUpdateStatus && $result;
        }

        return $this->respondWithData($resultUpdateStatus);
    }
}
