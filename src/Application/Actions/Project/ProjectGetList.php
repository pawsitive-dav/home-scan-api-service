<?php

declare(strict_types=1);

namespace App\Application\Actions\Project;

use Psr\Http\Message\ResponseInterface as Response;

class ProjectGetList extends MainAction
{
    protected function action(): Response
    {
        $pdo = $this->pdoConnect($_ENV['DB_PORTAL']);

        $sqlQuery = "SELECT
                        pd.project_id,
                        pd.project_status,
                        pd.project_name,
                        pd.project_note,
                        ptd.project_type,
                        ptd.type_address,
                        ptd.type_usable_area,
                        pc.customer_name,
                        pc.customer_phone,
                        pc.customer_email,
                        pco.coordinator_name,
                        pco.coordinator_phone,
                        pco.coordinator_email,
                        pf.image_path AS project_image,
                        COUNT(id.project_id) AS inspection_count,
                        JSON_OBJECT(
                            'avatar_path', mi_project_owner.avatar_path,
                            'first_name', mi_project_owner.first_name,
                            'last_name', mi_project_owner.last_name,
                            'code_name', mi_project_owner.code_name
                        ) AS project_owner,
                        JSON_OBJECT(
                            'account_id', mi_checker_supervisor.account_id,
                            'avatar_path', mi_checker_supervisor.avatar_path,
                            'first_name', mi_checker_supervisor.first_name,
                            'last_name', mi_checker_supervisor.last_name,
                            'code_name', mi_checker_supervisor.code_name
                        ) AS checker_supervisor
                    FROM project_detail AS pd
                    INNER JOIN project_type_detail ptd ON pd.project_id = ptd.project_id
                    INNER JOIN project_customer pc ON pd.project_id = pc.project_id
                    INNER JOIN project_coordinator pco ON pd.project_id = pco.project_id
                    INNER JOIN project_teams pt ON pd.project_id = pt.project_id
                    LEFT JOIN inspection_detail id ON pd.project_id = id.project_id
                    LEFT JOIN project_file pf ON pd.project_id = pf.project_id AND pf.file_type = 'main'
                    LEFT JOIN member_info mi_project_owner ON pt.project_owner = mi_project_owner.account_id
                    LEFT JOIN member_info mi_checker_supervisor ON pt.checker_supervisor = mi_checker_supervisor.account_id
                    GROUP BY
                        pd.project_id,
                        pd.project_status,
                        pd.project_name,
                        pd.project_note,
                        ptd.project_type,
                        ptd.type_address,
                        ptd.type_usable_area,
                        pc.customer_name,
                        pc.customer_phone,
                        pc.customer_email,
                        pco.coordinator_name,
                        pco.coordinator_phone,
                        pco.coordinator_email,
                        pf.image_path,
                        mi_project_owner.avatar_path,
                        mi_project_owner.first_name,
                        mi_project_owner.last_name,
                        mi_project_owner.code_name,
                        mi_checker_supervisor.account_id,
                        mi_checker_supervisor.avatar_path,
                        mi_checker_supervisor.first_name,
                        mi_checker_supervisor.last_name,
                        mi_checker_supervisor.code_name";

        $stmt = $pdo->prepare($sqlQuery);
        $stmt->execute();

        $allData = $stmt->fetchAll();

        if (empty($allData)) {
            return $this->respondWithData($allData);
        }

        $jsonData = [];
        foreach ($allData as $data) {
            $jsonData[] = [
                "project_id" => $data["project_id"],
                "project_image" => $data["project_image"],
                "project_status" => $data["project_status"],
                "project_name" => $data["project_name"],
                "project_note" => $data["project_note"],
                "type_name" => $data["project_type"],
                "type_address" => $data["type_address"],
                "type_usable_area" => $data["type_usable_area"],
                "inspection_count" => $data["inspection_count"],
                "customer" => [
                    "customer_name" => $data["customer_name"],
                    "customer_phone" => $data["customer_phone"],
                    "customer_email" => $data["customer_email"],
                ],
                "coordinator" => [
                    "coordinator_name" => $data["coordinator_name"],
                    "coordinator_phone" => $data["coordinator_phone"],
                    "coordinator_email" => $data["coordinator_email"],
                ],
                "project_owner" => json_decode($data["project_owner"]),
                "checker_supervisor" => json_decode($data["checker_supervisor"])
            ];
        }

        return $this->respondWithData($jsonData);
    }
}
