<?php

declare(strict_types=1);

namespace App\Application\Actions\Customer;

use Psr\Http\Message\ResponseInterface as Response;

class SettingServiceEdit extends MainAction
{
    protected function action(): Response
    {
        $input = $this->getFormData();
        $allKeys = ["service_id", "service_group", "service_name"];
        $requiredKeys = ["service_id", "service_group", "service_name"];

        if (!$this->validateInputBody($input, $allKeys, $requiredKeys)) {
            return $this->respondWithData("Bad Request", 400);
        }
        $PDO = $this->pdoConnect($_ENV['DB_PORTAL']);

        $sqlQuery = "UPDATE setting_services
                    SET 
                        service_group = :service_group, 
                        service_name = :service_name 
                    WHERE 
                        service_id = :service_id";

        $stmt = $PDO->prepare($sqlQuery);
        $stmt->bindValue(':service_id', $input['service_id']);
        $stmt->bindValue(':service_group', $input['service_group']);
        $stmt->bindValue(':service_name', $input['service_name']);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            return $this->respondWithData("Edited", 200);
        } else {
            return $this->respondWithData("Failed", 400);
        }
    }
}
