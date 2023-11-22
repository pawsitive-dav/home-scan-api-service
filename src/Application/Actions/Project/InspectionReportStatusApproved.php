<?php

declare(strict_types=1);

namespace App\Application\Actions\Project;

use Psr\Http\Message\ResponseInterface as Response;

class InspectionReportStatusApproved extends MainAction
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
        $accountId = $this->request->getAttribute('tokenInfo')->data;

        $sqlQuery = "UPDATE report_list SET
                            report_status = 'approved',
                            approved_at = :approved_at,
                            approved_by = :approved_by
                            WHERE project_id = :project_id
                            AND inspection_id = :inspection_id
                            AND report_id = :report_id";

        $stmt = $pdo->prepare($sqlQuery);
        $stmt->bindValue(':approved_at', $dateNow);
        $stmt->bindValue(':approved_by', $accountId);
        $stmt->bindValue(':project_id', $input['project_id']);
        $stmt->bindValue(':inspection_id', $input['inspection_id']);
        $stmt->bindValue(':report_id', $input['report_id']);
        $result = $stmt->execute();
        if (!$result) return $this->respondWithData("Update Report Status Fail", 404);

        $sqlQuery = "UPDATE project_detail SET
                            project_status = 'done',
                            status_update_at = :status_update_at
                            WHERE project_id = :project_id";

        $stmt = $pdo->prepare($sqlQuery);
        $stmt->bindValue(':status_update_at', $dateNow);
        $stmt->bindValue(':project_id', $input['project_id']);
        $result = $stmt->execute();
        if (!$result) return $this->respondWithData("Update Project Statsu Fail", 404);

        return $this->respondWithData("Success");
    }
}
