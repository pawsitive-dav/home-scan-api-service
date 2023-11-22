<?php

declare(strict_types=1);

namespace App\Application\Actions\Project;

use Psr\Http\Message\ResponseInterface as Response;

class InspcetionGetSystemDeflectList extends MainAction
{
    protected function action(): Response
    {
        $input = $this->getFormData();
        $allKeys = ["project_id", "inspection_id", "system_id"];
        $requiredKeys = ["project_id", "inspection_id", "system_id"];

        if (!$this->validateInputBody($input, $allKeys, $requiredKeys)) {
            return $this->respondWithData("Bad Request", 400);
        }

        $pdo = $this->pdoConnect($_ENV['DB_PORTAL']);

        $sqlQuery = "SELECT
                        isd.id, 
                        isd.image_id, 
                        isd.deflect_status, 
                        isd.deflect_detail, 
                        isd.created_at, 
                        JSON_OBJECT(
                            'avatar_path', mi_created_by.avatar_path,
                            'first_name', mi_created_by.first_name,
                            'last_name', mi_created_by.last_name,
                            'code_name', mi_created_by.code_name
                        ) AS created_by,
                        isd.updated_at, 
                        JSON_OBJECT(
                            'avatar_path', mi_updated_by.avatar_path,
                            'first_name', mi_updated_by.first_name,
                            'last_name', mi_updated_by.last_name,
                            'code_name', mi_updated_by.code_name
                        ) AS updated_by,
                        isd.update_status_at, 
                        JSON_OBJECT(
                            'avatar_path', mi_update_status_by.avatar_path,
                            'first_name', mi_update_status_by.first_name,
                            'last_name', mi_update_status_by.last_name,
                            'code_name', mi_update_status_by.code_name
                        ) AS update_status_by,
                        isto.image_path,
                        isto.image_name
                    FROM inspection_system_deflect isd
                    LEFT JOIN member_info mi_created_by ON isd.created_by = mi_created_by.account_id
                    LEFT JOIN member_info mi_updated_by ON isd.updated_by = mi_updated_by.account_id
                    LEFT JOIN member_info mi_update_status_by ON isd.update_status_by = mi_update_status_by.account_id
                    LEFT JOIN inspection_storage isto ON isd.image_id = isto.image_id
                    WHERE 
                        isd.project_id = :project_id 
                        AND isd.inspection_id = :inspection_id 
                        AND isd.system_id = :system_id";

        $stmt = $pdo->prepare($sqlQuery);
        $stmt->bindValue(':project_id', $input['project_id']);
        $stmt->bindValue(':inspection_id', $input['inspection_id']);
        $stmt->bindValue(':system_id', $input['system_id']);
        $stmt->execute();

        $allData = $stmt->fetchAll();
        if (empty($allData)) {
            return $this->respondWithData($allData);
        }

        $jsonData = [];
        foreach ($allData as $data) {
            $jsonData[] = [
                "id" => $data["id"],
                "image_id" => $data["image_id"],
                "deflect_status" => $data["deflect_status"],
                "deflect_detail" => $data["deflect_detail"],
                "created_at" => $data["created_at"],
                "created_by" => json_decode($data["created_by"]),
                "updated_at" => $data["updated_at"],
                "updated_by" => json_decode($data["updated_by"]),
                "update_status_at" => $data["update_status_at"],
                "update_status_by" => json_decode($data["update_status_by"]),
                "image_path" => $data["image_path"],
                "image_name" => $data["image_name"]
            ];
        }

        return $this->respondWithData($jsonData);
    }
}
