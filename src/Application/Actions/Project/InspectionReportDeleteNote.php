<?php

declare(strict_types=1);

namespace App\Application\Actions\Project;

use Psr\Http\Message\ResponseInterface as Response;

class InspectionReportDeleteNote extends MainAction
{
    protected function action(): Response
    {
        $input = $this->getFormData();
        $allKeys = ["report_id", "report_note_id"];
        $requiredKeys = ["report_id", "report_note_id"];

        if (!$this->validateInputBody($input, $allKeys, $requiredKeys)) {
            return $this->respondWithData("Bad Request", 400);
        }

        $pdo = $this->pdoConnect($_ENV['DB_PORTAL']);

        $sqlQuery = "DELETE FROM report_note 
                    WHERE report_id = :report_id 
                    AND report_note_id = :report_note_id";

        $stmt = $pdo->prepare($sqlQuery);
        $stmt->bindValue(':report_id', $input['report_id']);
        $stmt->bindValue(':report_note_id', $input['report_note_id']);
        $result = $stmt->execute();

        if (!$result) return $this->respondWithData("Delete Note Fail", 404);

        $sqlQuery = "DELETE FROM report_note_list 
                    WHERE report_id = :report_id 
                    AND report_note_id = :report_note_id";

        $stmt = $pdo->prepare($sqlQuery);
        $stmt->bindValue(':report_id', $input['report_id']);
        $stmt->bindValue(':report_note_id', $input['report_note_id']);
        $result = $stmt->execute();

        if (!$result) return $this->respondWithData("Delete Note Fail", 404);

        return $this->respondWithData("Delete Note Success");
    }
}
