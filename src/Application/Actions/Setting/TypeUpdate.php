<?php

declare(strict_types=1);

namespace App\Application\Actions\Setting;

use Psr\Http\Message\ResponseInterface as Response;

class TypeUpdate extends MainAction
{
    protected function action(): Response
    {
        $input = $this->getFormData();
        $allKeys = ["type_id", "type_name"];
        $requiredKeys = ["type_id", "type_name"];

        if (!$this->validateInputBody($input, $allKeys, $requiredKeys)) {
            return $this->respondWithData("Bad Request", 400);
        }

        $PDO = $this->pdoConnect($_ENV['DB_PORTAL']);

        if (!$this->checkType($input['type_name'], $PDO)) {
            return $this->respondWithData("Type already exists", 409);
        }

        date_default_timezone_set("Asia/Bangkok");
        $DATE_NOW = date('Y-m-d H:i:s');
        $account_id = $this->request->getAttribute('tokenInfo')->data;

        $sqlQuery = "UPDATE type_project
                     SET 
                        type_name = :type_name,
                        updated_at = :updated_at,
                        updated_by = :updated_by
                     WHERE 
                        type_id = :type_id";

        $stmt = $PDO->prepare($sqlQuery);
        $stmt->bindValue(':type_id', $input['type_id']);
        $stmt->bindValue(':type_name', $input['type_name']);
        $stmt->bindValue(':updated_at', $DATE_NOW);
        $stmt->bindValue(':updated_by', $account_id);

        if (!$stmt->execute()) {
            return $this->respondWithData("Failed to update type", 404);
        }

        return $this->respondWithData("Type Updated Successfully.", 200);
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
