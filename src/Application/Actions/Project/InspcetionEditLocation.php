<?php

declare(strict_types=1);

namespace App\Application\Actions\Project;

use Psr\Http\Message\ResponseInterface as Response;

class InspcetionEditLocation extends MainAction
{
    protected function action(): Response
    {
        $input = $this->getFormData();

        $allKeys = ["project_id", "inspection_id", "location_id", "location_name"];
        $requiredKeys = ["project_id", "inspection_id", "location_id", "location_name"];

        if (!$this->validateInputBody($input, $allKeys, $requiredKeys)) {
            return $this->respondWithData("Bad Request", 400);
        }

        $pdo = $this->pdoConnect($_ENV['DB_PORTAL']);

        $checkSql = "SELECT COUNT(*) as count FROM inspection_location 
                    WHERE project_id = :project_id 
                    AND inspection_id = :inspection_id 
                    AND location_name = :location_name";
        $checkStmt = $pdo->prepare($checkSql);
        $checkStmt->bindValue(':project_id', $input['project_id']);
        $checkStmt->bindValue(':inspection_id', $input['inspection_id']);
        $checkStmt->bindValue(':location_name', $input['location_name']);
        $checkStmt->execute();
        $count = $checkStmt->fetchColumn();

        if ($count > 0) {
            return $this->respondWithData("Location name already exists", 409);
        }

        $sqlQuery = "UPDATE inspection_location
                    SET
                        location_name = :location_name 
                    WHERE
                        project_id = :project_id
                        AND inspection_id = :inspection_id
                        AND location_id = :location_id";


        $stmt = $pdo->prepare($sqlQuery);
        $stmt->bindValue(':location_name', $input['location_name']);
        $stmt->bindValue(':project_id', $input['project_id']);
        $stmt->bindValue(':inspection_id', $input['inspection_id']);
        $stmt->bindValue(':location_id', $input['location_id']);

        $result = $stmt->execute();

        if (!$result) {
            return $this->respondWithData("Fail", 404);
        } else {
            return $this->respondWithData("Success");
        }
    }
}
