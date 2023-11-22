<?php

declare(strict_types=1);

namespace App\Application\Actions\Project;

use Psr\Http\Message\ResponseInterface as Response;

class InspcetionGetImageList extends MainAction
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

        $sqlQuery = "SELECT 
                        ist.project_id,
                        ist.inspection_id,
                        ist.image_id,
                        ist.image_name,
                        ist.image_path,
                        ist.image_size,
                        ist.uploaded_at,
                        JSON_OBJECT(
                            'avatar_path', mi.avatar_path,
                            'first_name', mi.first_name,
                            'last_name', mi.last_name,
                            'code_name', mi.code_name
                        ) AS uploaded_by,
                        ild.image_id AS location_tag,
                        isd.image_id AS system_tag
                    FROM 
                        inspection_storage ist
                        INNER JOIN member_info mi ON ist.uploaded_by = mi.account_id 
                        LEFT JOIN inspection_location_deflect ild ON ist.image_id = ild.image_id 
                        LEFT JOIN inspection_system_deflect isd ON ist.image_id = isd.image_id 
                    WHERE 
                        ist.project_id = :project_id AND ist.inspection_id = :inspection_id";

        $stmt = $pdo->prepare($sqlQuery);
        $stmt->bindValue(':project_id', $input['project_id']);
        $stmt->bindValue(':inspection_id', $input['inspection_id']);
        $stmt->execute();

        $allData = $stmt->fetchAll();

        if (empty($allData)) {
            return $this->respondWithData("No data found");
        }

        $jsonData = [];
        foreach ($allData as $data) {
            $jsonData[] = [
                "project_id" => $data["project_id"],
                "inspection_id" => $data["inspection_id"],
                "image_id" => $data["image_id"],
                "image_name" => $data["image_name"],
                "image_path" => $data["image_path"],
                "image_size" => $data["image_size"],
                "uploaded_at" => $data["uploaded_at"],
                "uploaded_by" => json_decode($data["uploaded_by"]),
                "location_tag" => $data["location_tag"],
                "system_tag" => $data["system_tag"]
            ];
        }

        return $this->respondWithData($jsonData);
    }
}
