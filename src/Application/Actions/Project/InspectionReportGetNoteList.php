<?php

declare(strict_types=1);

namespace App\Application\Actions\Project;

use Psr\Http\Message\ResponseInterface as Response;

class InspectionReportGetNoteList extends MainAction
{
    protected function action(): Response
    {
        $input = $this->getFormData();
        $allKeys = ["report_id"];
        $requiredKeys = ["report_id"];

        if (!$this->validateInputBody($input, $allKeys, $requiredKeys)) {
            return $this->respondWithData("Bad Request", 400);
        }

        $pdo = $this->pdoConnect($_ENV['DB_PORTAL']);

        // Member query
        $memberQuery = "SELECT 
                    account_id, 
                    avatar_path,
                    code_name, 
                    first_name,
                    last_name
                FROM member_info
                WHERE account_id = :account_id";

        // Query for report_note table
        $sqlQueryNote = "SELECT 
                    id,
                    report_note_id,
                    report_title,
                    created_at,
                    updated_at,
                    created_by,
                    updated_by
                FROM report_note
                WHERE report_id = :report_id";

        $stmtNote = $pdo->prepare($sqlQueryNote);
        $stmtNote->bindValue(':report_id', $input['report_id']);
        $stmtNote->execute();

        $reportNoteData = $stmtNote->fetchAll();

        // Loop through each report_note record and fetch corresponding member information for created_by
        foreach ($reportNoteData as &$note) {
            // Query for created_by member information
            $stmtCreatedBy = $pdo->prepare($memberQuery);
            $stmtCreatedBy->bindValue(':account_id', $note['created_by']);
            $stmtCreatedBy->execute();
            $note['created_by'] = $stmtCreatedBy->fetch();

            // Query for updated_by member information
            $stmtUpdatedBy = $pdo->prepare($memberQuery);
            $stmtUpdatedBy->bindValue(':account_id', $note['updated_by']);
            $stmtUpdatedBy->execute();
            $note['updated_by'] = $stmtUpdatedBy->fetch();

            // Query for report_note_list table
            $sqlQueryList = "SELECT 
                        id,
                        list_message,
                        report_note_id
                    FROM report_note_list
                    WHERE report_note_id = :report_note_id";

            $stmtList = $pdo->prepare($sqlQueryList);
            $stmtList->bindValue(':report_note_id', $note['report_note_id']);
            $stmtList->execute();

            $note['note_list'] = $stmtList->fetchAll();
        }

        return $this->respondWithData($reportNoteData);
    }
}
