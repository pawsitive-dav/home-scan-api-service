<?php

declare(strict_types=1);

namespace App\Application\Actions\Project;

use Psr\Http\Message\ResponseInterface as Response;

class InspectionGetReport extends MainAction
{
    protected function action(): Response
    {
        $pdo = $this->pdoConnect($_ENV['DB_PORTAL']);

        $sqlQuery = "SELECT 
                        rl.project_id,
                        rl.inspection_id,
                        rl.report_id,
                        rl.report_status,
                        rl.report_path,
                        rl.created_at,
                        JSON_OBJECT(
                            'avatar_path', mi_created_by.avatar_path,
                            'first_name', mi_created_by.first_name,
                            'last_name', mi_created_by.last_name,
                            'code_name', mi_created_by.code_name
                        ) AS created_by,
                        rl.approved_at,
                        JSON_OBJECT(
                            'avatar_path', mi_approved_by.avatar_path,
                            'first_name', mi_approved_by.first_name,
                            'last_name', mi_approved_by.last_name,
                            'code_name', mi_approved_by.code_name
                        ) AS approved_by,
                        pd.project_name,
                        pf.image_path,
                        id.inspection_no,
                        id.working_date
                    FROM report_list rl
                    LEFT JOIN member_info mi_created_by ON rl.created_by = mi_created_by.account_id
                    LEFT JOIN member_info mi_approved_by ON rl.approved_by = mi_approved_by.account_id
                    LEFT JOIN inspection_detail id ON rl.inspection_id = id.inspection_id
                    LEFT JOIN project_file pf ON rl.project_id = pf.project_id AND pf.file_type = 'main'
                    LEFT JOIN project_detail pd ON rl.project_id = pd.project_id";

        $stmt = $pdo->prepare($sqlQuery);
        $stmt->execute();

        $allData = $stmt->fetchAll();

        $jsonData = [];
        foreach ($allData as $data) {
            $jsonData[] = [
                "project_id" => $data["project_id"],
                "inspection_id" => $data["inspection_id"],
                "report_id" => $data["report_id"],
                "report_status" => $data["report_status"],
                "report_path" => $data["report_path"],
                "created_at" => $data["created_at"],
                "created_by" => json_decode($data["created_by"]),
                "approved_at" => $data["approved_at"],
                "approved_by" => json_decode($data["approved_by"]),
                "project_name" => $data["project_name"],
                "image_path" => $data["image_path"],
                "inspection_no" => $data["inspection_no"],
                "working_date" => $data["working_date"],
            ];
        }

        return $this->respondWithData($jsonData);
    }
}
