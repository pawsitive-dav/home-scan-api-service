<?php

declare(strict_types=1);

namespace App\Application\Actions\Setting;

use Psr\Http\Message\ResponseInterface as Response;

class SystemUpdate extends MainAction
{
    protected function action(): Response
    {
        $input = $this->getFormData();
        $allKeys = ["system_id", "system_name"];
        $requiredKeys = ["system_id", "system_name"];

        if (!$this->validateInputBody($input, $allKeys, $requiredKeys)) {
            return $this->respondWithData("Bad Request", 400);
        }

        $PDO = $this->pdoConnect($_ENV['DB_PORTAL']);

        if (!$this->checksystem($input['system_name'], $PDO)) {
            return $this->respondWithData("System already exists", 409);
        }

        date_default_timezone_set("Asia/Bangkok");
        $DATE_NOW = date('Y-m-d H:i:s');
        $account_id = $this->request->getAttribute('tokenInfo')->data;

        $sqlQuery = "UPDATE type_system
                     SET 
                        system_name = :system_name,
                        updated_at = :updated_at,
                        updated_by = :updated_by
                     WHERE 
                        system_id = :system_id";

        $stmt = $PDO->prepare($sqlQuery);
        $stmt->bindValue(':system_id', $input['system_id']);
        $stmt->bindValue(':system_name', $input['system_name']);
        $stmt->bindValue(':updated_at', $DATE_NOW);
        $stmt->bindValue(':updated_by', $account_id);

        if (!$stmt->execute()) {
            return $this->respondWithData("Failed to update system", 404);
        }

        return $this->respondWithData("System Updated Successfully.", 200);
    }

    private function checksystem($systemName, $PDO)
    {
        $sqlQuery = "SELECT * FROM type_system WHERE system_name = :system_name";
        $stmt = $PDO->prepare($sqlQuery);
        $stmt->bindValue(':system_name', $systemName);
        $stmt->execute();
        if ($stmt->rowCount() > 0) return false;
        return true;
    }
}
