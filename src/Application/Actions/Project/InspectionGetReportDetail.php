<?php

declare(strict_types=1);

namespace App\Application\Actions\Project;

use Psr\Http\Message\ResponseInterface as Response;

class InspectionGetReportDetail extends MainAction
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
                rl.project_id,
                MAX(rl.inspection_id) AS inspection_id,
                MAX(rl.report_id) AS report_id,
                MAX(rl.report_status) AS report_status,
                MAX(rl.report_path) AS report_path,
                MAX(rl.approved_at) AS approved_at,
                JSON_OBJECT(
                    'avatar_path', MAX(mi_approved.avatar_path),
                    'first_name', MAX(mi_approved.first_name),
                    'last_name', MAX(mi_approved.last_name),
                    'code_name', MAX(mi_approved.code_name)
                ) AS approved_by,
                JSON_OBJECT(
                    'account_id', MAX(mi.account_id),
                    'avatar_path', MAX(mi.avatar_path),
                    'first_name', MAX(mi.first_name),
                    'last_name', MAX(mi.last_name),
                    'code_name', MAX(mi.code_name)
                ) AS checker_supervisor,
                JSON_OBJECT(
                    'project_name', MAX(pd.project_name),
                    'working_date', MAX(id.working_date),
                    'inspection_no', MAX(id.inspection_no)
                ) AS project_detail,
                JSON_OBJECT(
                    'name', MAX(pc.customer_name),
                    'phone', MAX(pc.customer_phone),
                    'email', MAX(pc.customer_email)
                ) AS customer_detail,
                JSON_OBJECT(
                    'name', MAX(pco.coordinator_name),
                    'phone', MAX(pco.coordinator_phone),
                    'email', MAX(pco.coordinator_email)
                ) AS coordinator_detail,
                JSON_OBJECT(
                    'project_type', MAX(ptd.project_type),
                    'type_address', MAX(ptd.type_address),
                    'type_usable_area', MAX(ptd.type_usable_area)
                ) AS type_detail,
                GROUP_CONCAT(
                    JSON_OBJECT(
                        'file_type', pf.file_type,
                        'image_path', pf.image_path
                    )
                ) AS project_file
            FROM report_list rl
            LEFT JOIN project_detail pd ON rl.project_id = pd.project_id
            LEFT JOIN project_teams pt ON rl.project_id = pt.project_id
            LEFT JOIN inspection_detail id ON rl.inspection_id = id.inspection_id
            LEFT JOIN project_customer pc ON rl.project_id = pc.project_id
            LEFT JOIN project_coordinator pco ON rl.project_id = pco.project_id
            LEFT JOIN project_type_detail ptd ON rl.project_id = ptd.project_id
            LEFT JOIN project_file pf ON rl.project_id = pf.project_id
            LEFT JOIN member_info mi ON pt.checker_supervisor = mi.account_id
            LEFT JOIN member_info mi_approved ON rl.approved_by = mi_approved.account_id
            WHERE rl.report_id = :report_id
            GROUP BY rl.project_id";

        $stmt = $pdo->prepare($sqlQuery);
        $stmt->bindValue(':report_id', $input['report_id']);
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
                "approved_at" => $data["approved_at"],
                "approved_by" => json_decode($data["approved_by"]),
                "checker_supervisor" => json_decode($data["checker_supervisor"]),
                "project_detail" => json_decode($data["project_detail"]),
                "customer_detail" => json_decode($data["customer_detail"]),
                "coordinator_detail" => json_decode($data["coordinator_detail"]),
                "type_detail" => json_decode($data["type_detail"]),
                "project_file" => json_decode('[' . $data["project_file"] . ']')
            ];
        }

        return $this->respondWithData($jsonData[0]);
    }
}
