<?php

declare(strict_types=1);

namespace App\Application\Actions\Setting;

use Psr\Http\Message\ResponseInterface as Response;

class LocationCreate extends MainAction
{
    protected function action(): Response
    {
        $input = $this->getFormData();
        $allKeys = ["location_name",];
        $requiredKeys = ["location_name"];

        if (!$this->validateInputBody($input, $allKeys, $requiredKeys)) {
            return $this->respondWithData("Bad Request", 400);
        }

        $PDO = $this->pdoConnect($_ENV['DB_PORTAL']);

        if (!$this->checkLocation($input['location_name'], $PDO)) {
            return $this->respondWithData("Location already exists", 409);
        }

        date_default_timezone_set("Asia/Bangkok");
        $DATE_NOW = date('Y-m-d H:i:s');
        $UUID = $this->UUIDV4();
        $account_id = $this->request->getAttribute('tokenInfo')->data;

        $sqlQuery = "INSERT INTO type_location
                            SET
                                location_id = :location_id, 
                                location_name = :location_name,
                                created_at = :created_at, 
                                created_by = :created_by";

        $stmt = $PDO->prepare($sqlQuery);
        $stmt->bindValue(':location_id', $UUID);
        $stmt->bindValue(':location_name', $input['location_name']);
        $stmt->bindValue(':created_at', $DATE_NOW);
        $stmt->bindValue(':created_by', $account_id);

        if (!$stmt->execute()) {
            return $this->respondWithData("Failed to create location", 404);
        }
        return $this->respondWithData("Location Create Successfully.", 201);
    }

    private function checkLocation($locationName, $PDO)
    {
        $sqlQuery = "SELECT * FROM type_location WHERE location_name = :location_name";
        $stmt = $PDO->prepare($sqlQuery);
        $stmt->bindValue(':location_name', $locationName);
        $stmt->execute();
        if ($stmt->rowCount() > 0) return false;
        return true;
    }
}
