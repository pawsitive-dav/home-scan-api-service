<?php

declare(strict_types=1);

namespace App\Application\Actions\Project;

use Psr\Http\Message\ResponseInterface as Response;

class InspcetionDeleteSystem extends MainAction
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

        $sqlQuery = "DELETE FROM inspection_system 
                    WHERE project_id = :project_id 
                    AND inspection_id = :inspection_id 
                    AND system_id = :system_id";

        $stmt = $pdo->prepare($sqlQuery);
        $stmt->bindValue(':project_id', $input['project_id']);
        $stmt->bindValue(':inspection_id', $input['inspection_id']);
        $stmt->bindValue(':system_id', $input['system_id']);
        $systemDeleteResult = $stmt->execute();

        if (empty($systemDeleteResult)) return $this->respondWithData("Delete System Fail", 404);

        $sqlQuery = "DELETE FROM inspection_system_deflect 
                    WHERE project_id = :project_id 
                    AND inspection_id = :inspection_id 
                    AND system_id = :system_id";

        $stmt = $pdo->prepare($sqlQuery);
        $stmt->bindValue(':project_id', $input['project_id']);
        $stmt->bindValue(':inspection_id', $input['inspection_id']);
        $stmt->bindValue(':system_id', $input['system_id']);
        $deflectDeleteResult = $stmt->execute();

        if (empty($deflectDeleteResult)) return $this->respondWithData("Delete Deflect Fail", 404);

        return $this->respondWithData("Delete System Success");
    }
}
