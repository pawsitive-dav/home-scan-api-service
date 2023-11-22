<?php

declare(strict_types=1);

namespace App\Application\Actions\Project;

use Psr\Http\Message\ResponseInterface as Response;

class InspectionCreateReport extends MainAction
{
    protected function action(): Response
    {
        $input = $this->getFormData();
        $allKeys = ["project_id", "inspection_id"];
        $requiredKeys = ["project_id", "inspection_id"];

        if (!$this->validateInputBody($input, $allKeys, $requiredKeys)) {
            return $this->respondWithData("Bad Request", 400);
        }

        $pdo = $this->pdoConnect($_ENV['DB_PORTAL']);

        date_default_timezone_set("Asia/Bangkok");
        $dateNow = date('Y-m-d H:i:s');
        $reportId = $this->UUIDV4();
        $accountId = $this->request->getAttribute('tokenInfo')->data;

        $sqlQuery = "INSERT INTO report_list
                        SET
                            project_id = :project_id, 
                            inspection_id = :inspection_id,
                            report_id = :report_id,
                            report_status = 'in-progress',
                            created_at = :created_at,
                            created_by = :created_by";

        $stmt = $pdo->prepare($sqlQuery);
        $stmt->bindValue(':project_id', $input['project_id']);
        $stmt->bindValue(':inspection_id', $input['inspection_id']);
        $stmt->bindValue(':report_id', $reportId);
        $stmt->bindValue(':created_at', $dateNow);
        $stmt->bindValue(':created_by', $accountId);
        $result = $stmt->execute();

        if (!$result) return $this->respondWithData("Create Report Fail", 404);

        return $this->respondWithData($reportId);
    }
}
