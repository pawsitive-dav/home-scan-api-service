<?php

declare(strict_types=1);

namespace App\Application\Actions\Setting;

use Psr\Http\Message\ResponseInterface as Response;

class DeflectUpdate extends MainAction
{
    protected function action(): Response
    {
        $input = $this->getFormData();
        $allKeys = ["deflect_id", "deflect_name"];
        $requiredKeys = ["deflect_id", "deflect_name"];

        if (!$this->validateInputBody($input, $allKeys, $requiredKeys)) {
            return $this->respondWithData("Bad Request", 400);
        }

        $PDO = $this->pdoConnect($_ENV['DB_PORTAL']);

        if (!$this->checkDeflect($input['deflect_name'], $PDO)) {
            return $this->respondWithData("Deflect already exists", 409);
        }

        date_default_timezone_set("Asia/Bangkok");
        $DATE_NOW = date('Y-m-d H:i:s');
        $account_id = $this->request->getAttribute('tokenInfo')->data;

        $sqlQuery = "UPDATE type_deflect
                     SET 
                        deflect_name = :deflect_name,
                        updated_at = :updated_at,
                        updated_by = :updated_by
                     WHERE 
                        deflect_id = :deflect_id";

        $stmt = $PDO->prepare($sqlQuery);
        $stmt->bindValue(':deflect_id', $input['deflect_id']);
        $stmt->bindValue(':deflect_name', $input['deflect_name']);
        $stmt->bindValue(':updated_at', $DATE_NOW);
        $stmt->bindValue(':updated_by', $account_id);

        if (!$stmt->execute()) {
            return $this->respondWithData("Failed to update deflect", 404);
        }

        return $this->respondWithData("Deflect Updated Successfully.", 200);
    }

    private function checkDeflect($deflectName, $PDO)
    {
        $sqlQuery = "SELECT * FROM type_deflect WHERE deflect_name = :deflect_name";
        $stmt = $PDO->prepare($sqlQuery);
        $stmt->bindValue(':deflect_name', $deflectName);
        $stmt->execute();
        if ($stmt->rowCount() > 0) return false;
        return true;
    }
}
