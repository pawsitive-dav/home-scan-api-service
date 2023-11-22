<?php

declare(strict_types=1);

namespace App\Application\Actions\Project;

use Psr\Http\Message\ResponseInterface as Response;

class InspcetionCreateDetail extends MainAction
{
    protected function action(): Response
    {
        $input = $this->getFormData();
        $allKeys = ["project_id", "working_date", "inspection_no"];
        $requiredKeys = ["project_id", "working_date", "inspection_no"];

        if (!$this->validateInputBody($input, $allKeys, $requiredKeys)) {
            return $this->respondWithData("Bad Request", 400);
        }

        date_default_timezone_set("Asia/Bangkok");
        $DATE_NOW = date('Y-m-d H:i:s');
        $inspectionId = $this->UUIDV4();
        $pdo = $this->pdoConnect($_ENV['DB_PORTAL']);
        $account_id = $this->request->getAttribute('tokenInfo')->data;

        $sqlQuery = "INSERT INTO inspection_detail
                            SET
                                project_id = :project_id, 
                                inspection_id = :inspection_id, 
                                working_date = :working_date, 
                                inspection_no = :inspection_no, 
                                created_at = :created_at, 
                                created_by = :created_by";

        $stmt = $pdo->prepare($sqlQuery);
        $stmt->bindValue(':project_id', $input['project_id']);
        $stmt->bindValue(':inspection_id', $inspectionId);
        $stmt->bindValue(':working_date', $input['working_date']);
        $stmt->bindValue(':inspection_no', $input['inspection_no']);
        $stmt->bindValue(':created_at', $DATE_NOW);
        $stmt->bindValue(':created_by', $account_id);
        $result = $stmt->execute();
        if (!$result) return $this->respondWithData("Create Inspaction Fail", 404);

        $sqlQuerySupervisor = "SELECT * FROM project_teams 
                        WHERE project_id = :project_id AND checker_supervisor IS NOT NULL";
        $stmtSupervisor = $pdo->prepare($sqlQuerySupervisor);
        $stmtSupervisor->bindValue(':project_id', $input['project_id']);
        $stmtSupervisor->execute();
        $hasSupervisorData = $stmtSupervisor->rowCount() > 0;

        $resultUpdateStatus = false;
        if ($hasSupervisorData) {
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
        }

        return $this->respondWithData($resultUpdateStatus);
    }
}
