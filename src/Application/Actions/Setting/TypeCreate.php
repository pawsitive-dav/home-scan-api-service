<?php

declare(strict_types=1);

namespace App\Application\Actions\Setting;

use Psr\Http\Message\ResponseInterface as Response;

class TypeCreate extends MainAction
{
    protected function action(): Response
    {
        $input = $this->getFormData();
        $allKeys = ["type_name",];
        $requiredKeys = ["type_name"];

        if (!$this->validateInputBody($input, $allKeys, $requiredKeys)) {
            return $this->respondWithData("Bad Request", 400);
        }

        $PDO = $this->pdoConnect($_ENV['DB_PORTAL']);

        if (!$this->checkType($input['type_name'], $PDO)) {
            return $this->respondWithData("Type already exists", 409);
        }

        date_default_timezone_set("Asia/Bangkok");
        $DATE_NOW = date('Y-m-d H:i:s');
        $UUID = $this->UUIDV4();
        $account_id = $this->request->getAttribute('tokenInfo')->data;

        $sqlQuery = "INSERT INTO type_project
                            SET
                                type_id = :type_id, 
                                type_name = :type_name, 
                                created_at = :created_at, 
                                created_by = :created_by";

        $stmt = $PDO->prepare($sqlQuery);
        $stmt->bindValue(':type_id', $UUID);
        $stmt->bindValue(':type_name', $input['type_name']);
        $stmt->bindValue(':created_at', $DATE_NOW);
        $stmt->bindValue(':created_by', $account_id);

        if (!$stmt->execute()) {
            return $this->respondWithData("Failed to create type", 404);
        }
        return $this->respondWithData("Type Create Successfully.", 201);
    }

    private function checkType($typeName, $PDO)
    {
        $sqlQuery = "SELECT * FROM type_project WHERE type_name = :type_name";
        $stmt = $PDO->prepare($sqlQuery);
        $stmt->bindValue(':type_name', $typeName);
        $stmt->execute();
        if ($stmt->rowCount() > 0) return false;
        return true;
    }
}
