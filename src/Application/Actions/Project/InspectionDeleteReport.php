<?php

declare(strict_types=1);

namespace App\Application\Actions\Project;

use Psr\Http\Message\ResponseInterface as Response;

class InspectionDeleteReport extends MainAction
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

        $sqlQuery = "DELETE FROM report_note_list 
                    WHERE project_id = :project_id 
                    AND inspection_id = :inspection_id
                    AND report_id = :report_id";

        $stmt = $pdo->prepare($sqlQuery);
        $stmt->bindValue(':project_id', $input['project_id']);
        $stmt->bindValue(':inspection_id', $input['inspection_id']);
        $stmt->bindValue(':report_id', $input['report_id']);
        $result = $stmt->execute();

        if (!$result) return $this->respondWithData("Delete Note List Fail", 404);

        $sqlQuery = "DELETE FROM report_note 
                    WHERE project_id = :project_id 
                    AND inspection_id = :inspection_id
                    AND report_id = :report_id";

        $stmt = $pdo->prepare($sqlQuery);
        $stmt->bindValue(':project_id', $input['project_id']);
        $stmt->bindValue(':inspection_id', $input['inspection_id']);
        $stmt->bindValue(':report_id', $input['report_id']);
        $result = $stmt->execute();

        if (!$result) return $this->respondWithData("Delete Note Fail", 404);

        $sqlQuery = "DELETE FROM report_list 
                    WHERE project_id = :project_id 
                    AND inspection_id = :inspection_id
                    AND report_id = :report_id";

        $stmt = $pdo->prepare($sqlQuery);
        $stmt->bindValue(':project_id', $input['project_id']);
        $stmt->bindValue(':inspection_id', $input['inspection_id']);
        $stmt->bindValue(':report_id', $input['report_id']);
        $result = $stmt->execute();

        if (!$result) return $this->respondWithData("Delete Report Fail", 404);

        return $this->respondWithData("Delete Report Success");
    }
}
