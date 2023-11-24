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

        $sqlQuery = "SELECT 
                    rn.report_note_id,
                    rn.report_title,
                    rn.created_at,
                    rn.updated_at,
                    JSON_OBJECT(
                        'avatar_path', mi_created_by.avatar_path,
                        'first_name', mi_created_by.first_name,
                        'last_name', mi_created_by.last_name,
                        'code_name', mi_created_by.code_name
                    ) AS created_by,
                    JSON_OBJECT(
                        'avatar_path', mi_updated_by.avatar_path,
                        'first_name', mi_updated_by.first_name,
                        'last_name', mi_updated_by.last_name,
                        'code_name', mi_updated_by.code_name
                    ) AS updated_by,
                    GROUP_CONCAT(
                        JSON_OBJECT(
                            'report_note_list_id', rnl.report_note_list_id,
                            'list_message', rnl.list_message
                        )
                    ) AS note_list
                    FROM report_note rn
                    LEFT JOIN member_info mi_created_by ON rn.created_by = mi_created_by.account_id
                    LEFT JOIN member_info mi_updated_by ON rn.updated_by = mi_updated_by.account_id
                    LEFT JOIN report_note_list rnl ON rn.report_note_id = rnl.report_note_id
                    WHERE rn.report_id = :report_id
                    GROUP BY rn.report_note_id";

        $stmt = $pdo->prepare($sqlQuery);
        $stmt->bindValue(':report_id', $input['report_id']);
        $result = $stmt->execute();

        if (!$result) return $this->respondWithData("Get Fail", 404);

        $allData = $stmt->fetchAll();

        $jsonData = [];
        foreach ($allData as $data) {
            $jsonData[] = [
                "report_note_id" => $data["report_note_id"],
                "report_title" => $data["report_title"],
                "created_at" => $data["created_at"],
                "updated_at" => $data["updated_at"],
                "created_by" => json_decode($data["created_by"]),
                "updated_by" => json_decode($data["updated_by"]),
                "note_list" => json_decode('[' . $data["note_list"] . ']')
            ];
        }

        return $this->respondWithData($jsonData);
    }
}
