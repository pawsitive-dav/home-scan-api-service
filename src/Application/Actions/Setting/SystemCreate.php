<?php

declare(strict_types=1);

namespace App\Application\Actions\Setting;

use Psr\Http\Message\ResponseInterface as Response;

class SystemCreate extends MainAction
{
    protected function action(): Response
    {
        $input = $this->getFormData();
        $allKeys = ["system_name",];
        $requiredKeys = ["system_name"];

        if (!$this->validateInputBody($input, $allKeys, $requiredKeys)) {
            return $this->respondWithData("Bad Request", 400);
        }

        $PDO = $this->pdoConnect($_ENV['DB_PORTAL']);

        if (!$this->checkSystem($input['system_name'], $PDO)) {
            return $this->respondWithData("system already exists", 409);
        }

        date_default_timezone_set("Asia/Bangkok");
        $DATE_NOW = date('Y-m-d H:i:s');
        $UUID = $this->UUIDV4();
        $account_id = $this->request->getAttribute('tokenInfo')->data;

        $sqlQuery = "INSERT INTO type_system
                            SET
                                system_id = :system_id, 
                                system_name = :system_name, 
                                created_at = :created_at, 
                                created_by = :created_by";

        $stmt = $PDO->prepare($sqlQuery);
        $stmt->bindValue(':system_id', $UUID);
        $stmt->bindValue(':system_name', $input['system_name']);
        $stmt->bindValue(':created_at', $DATE_NOW);
        $stmt->bindValue(':created_by', $account_id);

        if (!$stmt->execute()) {
            return $this->respondWithData("Failed to create system", 404);
        }
        return $this->respondWithData("System Create Successfully.", 201);
    }

    private function checkSystem($systemName, $PDO)
    {
        $sqlQuery = "SELECT * FROM type_system WHERE system_name = :system_name";
        $stmt = $PDO->prepare($sqlQuery);
        $stmt->bindValue(':system_name', $systemName);
        $stmt->execute();
        if ($stmt->rowCount() > 0) return false;
        return true;
    }
}
