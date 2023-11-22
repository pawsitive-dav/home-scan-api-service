<?php

declare(strict_types=1);

namespace App\Application\Actions\Project;

use Psr\Http\Message\ResponseInterface as Response;

class InspectionReportAddNote extends MainAction
{
    protected function action(): Response
    {
        $input = $this->getFormData();
        $allKeys = ["project_id", "inspection_id", "report_id", "report_title", "report_description", "note_list"];
        $requiredKeys = ["project_id", "inspection_id", "report_id", "report_title"];

        if (!$this->validateInputBody($input, $allKeys, $requiredKeys)) {
            return $this->respondWithData("Bad Request", 400);
        }

        $pdo = $this->pdoConnect($_ENV['DB_PORTAL']);

        date_default_timezone_set("Asia/Bangkok");
        $dateNow = date('Y-m-d H:i:s');
        $reportNoteId = $this->UUIDV4();
        $accountId = $this->request->getAttribute('tokenInfo')->data;

        $sqlQuery = "INSERT INTO report_note
                        SET
                            project_id = :project_id, 
                            inspection_id = :inspection_id,
                            report_id = :report_id,
                            report_note_id = :report_note_id,
                            report_title = :report_title,
                            report_description = :report_description,
                            created_at = :created_at,
                            created_by = :created_by";

        $stmt = $pdo->prepare($sqlQuery);
        $stmt->bindValue(':project_id', $input['project_id']);
        $stmt->bindValue(':inspection_id', $input['inspection_id']);
        $stmt->bindValue(':report_id', $input['report_id']);
        $stmt->bindValue(':report_note_id', $reportNoteId);
        $stmt->bindValue(':report_title', $input['report_title']);
        $stmt->bindValue(':report_description', $input['report_description']);
        $stmt->bindValue(':created_at', $dateNow);
        $stmt->bindValue(':created_by', $accountId);
        $result = $stmt->execute();

        if (!$result) return $this->respondWithData("Add Fail", 404);

        if (count($input['note_list']) != 0) {
            $sqlQuery = "INSERT INTO report_note_list
                            SET
                                project_id = :project_id, 
                                inspection_id = :inspection_id,
                                report_id = :report_id,
                                report_note_id = :report_note_id,
                                report_note_list_id = :report_note_list_id,
                                list_message = :list_message";

            foreach ($input['note_list'] as $value) {
                $reportNoteListId = $this->UUIDV4();
                $stmt = $pdo->prepare($sqlQuery);
                $stmt->bindValue(':project_id', $input['project_id']);
                $stmt->bindValue(':inspection_id', $input['inspection_id']);
                $stmt->bindValue(':report_id', $input['report_id']);
                $stmt->bindValue(':report_note_id', $reportNoteId);
                $stmt->bindValue(':report_note_list_id', $reportNoteListId);
                $stmt->bindValue(':list_message', $value);
                $result = $stmt->execute();
                if (!$result) return $this->respondWithData("Add List Fail", 404);
            }
        }

        return $this->respondWithData("Added");
    }
}
