<?php

declare(strict_types=1);

namespace App\Application\Actions\Project;

use Psr\Http\Message\ResponseInterface as Response;

class InspectionReportGetLocationList extends MainAction
{
    protected function action(): Response
    {
        $input = $this->getFormData();
        $allKeys = ["project_id", "inspection_id"];
        $requiredKeys = ["project_id", "inspection_id"];

        if (!$this->validateInputBody($input, $allKeys, $requiredKeys)) {
            return $this->respondWithData("Bad Request", 400);
        }

        $pdo = $this->pdoConnect($_ENV['DB_PORTAL']);

        $sqlQuery =
            "SELECT 
                    il.inspection_id,
                    il.location_id,
                    il.location_name,
                    GROUP_CONCAT(
                        JSON_OBJECT(
                            'id', ild.id,
                            'image_id', ild.image_id,
                            'image_name', isto.image_name,
                            'image_path', isto.image_path,
                            'image_size', isto.image_size,
                            'image_uploaded_at', isto.uploaded_at,
                            'image_uploaded_by', JSON_OBJECT(
                                'avatar_path', image_updated_by.avatar_path,
                                'first_name', image_updated_by.first_name,
                                'last_name', image_updated_by.last_name,
                                'code_name', image_updated_by.code_name
                            ),
                            'deflect_status', ild.deflect_status,
                            'deflect_detail', ild.deflect_detail,
                            'created_at', ild.created_at,
                            'created_by', JSON_OBJECT(
                                'avatar_path', deflect_created_by.avatar_path,
                                'first_name', deflect_created_by.first_name,
                                'last_name', deflect_created_by.last_name,
                                'code_name', deflect_created_by.code_name
                            ),
                            'updated_at', ild.updated_at,
                            'updated_by', JSON_OBJECT(
                                'avatar_path', deflect_updated_by.avatar_path,
                                'first_name', deflect_updated_by.first_name,
                                'last_name', deflect_updated_by.last_name,
                                'code_name', deflect_updated_by.code_name
                            ),
                            'update_status_at', ild.update_status_at,
                            'update_status_by', JSON_OBJECT(
                                'avatar_path', deflect_update_status_by.avatar_path,
                                'first_name', deflect_update_status_by.first_name,
                                'last_name', deflect_update_status_by.last_name,
                                'code_name', deflect_update_status_by.code_name
                            )
                        )
                    ) AS deflect_list
                    FROM inspection_location il
                    LEFT JOIN inspection_location_deflect ild ON il.location_id = ild.location_id
                    LEFT JOIN inspection_storage isto ON ild.image_id = isto.image_id
                    LEFT JOIN member_info deflect_created_by ON ild.created_by = deflect_created_by.account_id
                    LEFT JOIN member_info deflect_updated_by ON ild.updated_by = deflect_updated_by.account_id
                    LEFT JOIN member_info image_updated_by ON isto.uploaded_by = image_updated_by.account_id
                    LEFT JOIN member_info deflect_update_status_by ON ild.update_status_by = deflect_update_status_by.account_id
                    WHERE il.project_id = :project_id AND il.inspection_id = :inspection_id
                    GROUP BY il.inspection_id, il.location_id, il.location_name";

        $stmt = $pdo->prepare($sqlQuery);
        $stmt->bindValue(':project_id', $input['project_id']);
        $stmt->bindValue(':inspection_id', $input['inspection_id']);
        $result = $stmt->execute();

        if (!$result) return $this->respondWithData("Get Fail", 404);

        $allData = $stmt->fetchAll();

        $jsonData = [];
        foreach ($allData as $data) {
            $deflectList = json_decode('[' . $data["deflect_list"] . ']', true);

            $jsonData[] = [
                "inspection_id" => $data["inspection_id"],
                "location_id" => $data["location_id"],
                "location_name" => $data["location_name"],
                "deflect_list" => array_map(function ($deflectItem) {
                    return [
                        'id' => $deflectItem['id'],
                        'image_id' => $deflectItem['image_id'],
                        'image_name' => $deflectItem['image_name'],
                        'image_path' => $deflectItem['image_path'],
                        'image_size' => $deflectItem['image_size'],
                        'image_uploaded_at' => $deflectItem['image_uploaded_at'],
                        'image_uploaded_by' => [
                            'avatar_path' => $deflectItem['image_uploaded_by']['avatar_path'],
                            'first_name' => $deflectItem['image_uploaded_by']['first_name'],
                            'last_name' => $deflectItem['image_uploaded_by']['last_name'],
                            'code_name' => $deflectItem['image_uploaded_by']['code_name'],
                        ],
                        'deflect_status' => $deflectItem['deflect_status'],
                        'deflect_detail' => $deflectItem['deflect_detail'],
                        'created_at' => $deflectItem['created_at'],
                        'created_by' => [
                            'avatar_path' => $deflectItem['created_by']['avatar_path'],
                            'first_name' => $deflectItem['created_by']['first_name'],
                            'last_name' => $deflectItem['created_by']['last_name'],
                            'code_name' => $deflectItem['created_by']['code_name'],
                        ],
                        'updated_at' => $deflectItem['updated_at'],
                        'updated_by' => [
                            'avatar_path' => $deflectItem['updated_by']['avatar_path'],
                            'first_name' => $deflectItem['updated_by']['first_name'],
                            'last_name' => $deflectItem['updated_by']['last_name'],
                            'code_name' => $deflectItem['updated_by']['code_name'],
                        ],
                        'update_status_at' => $deflectItem['update_status_at'],
                        'update_status_by' => [
                            'avatar_path' => $deflectItem['update_status_by']['avatar_path'],
                            'first_name' => $deflectItem['update_status_by']['first_name'],
                            'last_name' => $deflectItem['update_status_by']['last_name'],
                            'code_name' => $deflectItem['update_status_by']['code_name'],
                        ],
                    ];
                }, $deflectList),
            ];
        }

        return $this->respondWithData($jsonData);
    }
}
