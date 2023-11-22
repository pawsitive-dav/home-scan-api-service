<?php

declare(strict_types=1);

namespace App\Application\Actions\Setting;

use Psr\Http\Message\ResponseInterface as Response;

class DeflectCreate extends MainAction
{
    protected function action(): Response
    {
        $input = $this->getFormData();
        $allKeys = ["deflect_name",];
        $requiredKeys = ["deflect_name"];

        if (!$this->validateInputBody($input, $allKeys, $requiredKeys)) {
            return $this->respondWithData("Bad Request", 400);
        }

        $PDO = $this->pdoConnect($_ENV['DB_PORTAL']);

        if (!$this->checkDeflect($input['deflect_name'], $PDO)) {
            return $this->respondWithData("Deflect already exists", 409);
        }

        date_default_timezone_set("Asia/Bangkok");
        $DATE_NOW = date('Y-m-d H:i:s');
        $UUID = $this->UUIDV4();
        $account_id = $this->request->getAttribute('tokenInfo')->data;

        $sqlQuery = "INSERT INTO type_deflect
                            SET
                                deflect_id = :deflect_id, 
                                deflect_name = :deflect_name, 
                                created_at = :created_at, 
                                created_by = :created_by";

        $stmt = $PDO->prepare($sqlQuery);
        $stmt->bindValue(':deflect_id', $UUID);
        $stmt->bindValue(':deflect_name', $input['deflect_name']);
        $stmt->bindValue(':created_at', $DATE_NOW);
        $stmt->bindValue(':created_by', $account_id);

        if (!$stmt->execute()) {
            return $this->respondWithData("Failed to create deflect", 404);
        }
        return $this->respondWithData("Deflect Create Successfully.", 201);
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
