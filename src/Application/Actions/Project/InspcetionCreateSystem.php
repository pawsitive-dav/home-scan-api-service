<?php

declare(strict_types=1);

namespace App\Application\Actions\Project;

use Psr\Http\Message\ResponseInterface as Response;

class InspcetionCreateSystem extends MainAction
{
    protected function action(): Response
    {
        $input = $this->getFormData();

        $allKeys = ["project_id", "inspection_id", "system_name"];
        $requiredKeys = ["project_id", "inspection_id", "system_name"];

        if (!$this->validateInputBody($input, $allKeys, $requiredKeys)) {
            return $this->respondWithData("Bad Request", 400);
        }

        $pdo = $this->pdoConnect($_ENV['DB_PORTAL']);

        $checkSql = "SELECT COUNT(*) as count FROM inspection_system 
                    WHERE project_id = :project_id 
                    AND inspection_id = :inspection_id 
                    AND system_name = :system_name";

        $checkStmt = $pdo->prepare($checkSql);
        $checkStmt->bindValue(':project_id', $input['project_id']);
        $checkStmt->bindValue(':inspection_id', $input['inspection_id']);
        $checkStmt->bindValue(':system_name', $input['system_name']);
        $checkStmt->execute();
        $count = $checkStmt->fetchColumn();

        if ($count > 0) {
            return $this->respondWithData("System name already exists", 409);
        }

        $systemId = $this->UUIDV4();

        $sqlQuery = "INSERT INTO inspection_system
                            SET
                                project_id = :project_id, 
                                inspection_id = :inspection_id, 
                                system_id = :system_id, 
                                system_name = :system_name";

        $stmt = $pdo->prepare($sqlQuery);
        $stmt->bindValue(':project_id', $input['project_id']);
        $stmt->bindValue(':inspection_id', $input['inspection_id']);
        $stmt->bindValue(':system_id', $systemId);
        $stmt->bindValue(':system_name', $input['system_name']);
        $result = $stmt->execute();

        if (!$result) {
            return $this->respondWithData("Fail", 404);
        } else {
            return $this->respondWithData("Success");
        }
    }
}
