<?php

declare(strict_types=1);

namespace App\Application\Actions\Project;

use Psr\Http\Message\ResponseInterface as Response;

class InspcetionGetSystemDeflectStorage extends MainAction
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
                        ist.image_id,
                        ist.image_name,
                        ist.image_path,
                        ild.image_id AS location_usage,
                        isd.image_id AS system_usage
                    FROM inspection_storage ist
                        LEFT JOIN inspection_location_deflect ild ON ist.image_id = ild.image_id 
                        LEFT JOIN inspection_system_deflect isd ON ist.image_id = isd.image_id 
                    WHERE 
                        ist.project_id = :project_id 
                        AND ist.inspection_id = :inspection_id
                        AND isd.image_id IS NULL";

        $stmt = $pdo->prepare($sqlQuery);
        $stmt->bindValue(':project_id', $input['project_id']);
        $stmt->bindValue(':inspection_id', $input['inspection_id']);
        $stmt->execute();

        $allData = $stmt->fetchAll();

        if (empty($allData)) {
            return $this->respondWithData("No data found");
        }

        return $this->respondWithData($allData);
    }
}
