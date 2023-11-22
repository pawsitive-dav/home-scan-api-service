<?php

declare(strict_types=1);

namespace App\Application\Actions\Project;

use Psr\Http\Message\ResponseInterface as Response;

class InspcetionDeleteLocation extends MainAction
{
    protected function action(): Response
    {
        $input = $this->getFormData();
        $allKeys = ["project_id", "inspection_id", "location_id"];
        $requiredKeys = ["project_id", "inspection_id", "location_id"];

        if (!$this->validateInputBody($input, $allKeys, $requiredKeys)) {
            return $this->respondWithData("Bad Request", 400);
        }

        $pdo = $this->pdoConnect($_ENV['DB_PORTAL']);

        $sqlQuery = "DELETE FROM inspection_location 
                    WHERE project_id = :project_id 
                    AND inspection_id = :inspection_id 
                    AND location_id = :location_id";

        $stmt = $pdo->prepare($sqlQuery);
        $stmt->bindValue(':project_id', $input['project_id']);
        $stmt->bindValue(':inspection_id', $input['inspection_id']);
        $stmt->bindValue(':location_id', $input['location_id']);
        $locationDeleteResult = $stmt->execute();

        if (empty($locationDeleteResult)) return $this->respondWithData("Delete Location Fail", 404);

        $sqlQuery = "DELETE FROM inspection_location_deflect 
                    WHERE project_id = :project_id 
                    AND inspection_id = :inspection_id 
                    AND location_id = :location_id";

        $stmt = $pdo->prepare($sqlQuery);
        $stmt->bindValue(':project_id', $input['project_id']);
        $stmt->bindValue(':inspection_id', $input['inspection_id']);
        $stmt->bindValue(':location_id', $input['location_id']);
        $deflectDeleteResult = $stmt->execute();

        if (empty($deflectDeleteResult)) return $this->respondWithData("Delete Deflect Fail", 404);

        return $this->respondWithData("Delete Location Success");
    }
}
