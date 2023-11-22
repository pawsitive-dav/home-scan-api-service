<?php

declare(strict_types=1);

namespace App\Application\Actions\Project;

use Psr\Http\Message\ResponseInterface as Response;

class InspectionReportStatusApproval extends MainAction
{
    protected function action(): Response
    {
        $input = $this->getFormData();
        $allKeys = ["project_id", "inspection_id", "report_id"];
        $requiredKeys = ["project_id", "inspection_id", "report_id"];

        if (!$this->validateInputBody($input, $allKeys, $requiredKeys)) {
            return $this->respondWithData("Bad Request", 400);
        }

        $pdo = $this->pdoConnect($_ENV['DB_PORTAL']);

        date_default_timezone_set("Asia/Bangkok");
        $dateNow = date('Y-m-d H:i:s');

        $sqlQuery = "UPDATE report_list SET
                            report_status = 'approval'
                            WHERE project_id = :project_id
                            AND inspection_id = :inspection_id
                            AND report_id = :report_id";

        $stmt = $pdo->prepare($sqlQuery);
        $stmt->bindValue(':project_id', $input['project_id']);
        $stmt->bindValue(':inspection_id', $input['inspection_id']);
        $stmt->bindValue(':report_id', $input['report_id']);
        $result = $stmt->execute();
        if (!$result) return $this->respondWithData("Update Report Status Fail", 404);

        $sqlQuery = "UPDATE project_detail SET
                            project_status = 'report-approval',
                            status_update_at = :status_update_at
                            WHERE project_id = :project_id";

        $stmt = $pdo->prepare($sqlQuery);
        $stmt->bindValue(':project_id', $input['project_id']);
        $stmt->bindValue(':status_update_at', $dateNow);
        $result = $stmt->execute();
        if (!$result) return $this->respondWithData("Update Project Statsu Fail", 404);

        return $this->respondWithData("Success");
    }
}
