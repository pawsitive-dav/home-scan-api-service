<?php

declare(strict_types=1);

namespace App\Application\Actions\Customer;

use Psr\Http\Message\ResponseInterface as Response;

class SettingServiceCreate extends MainAction
{
    protected function action(): Response
    {
        $input = $this->getFormData();
        $allKeys = ["service_group", "service_name"];
        $requiredKeys = ["service_group", "service_name"];

        if (!$this->validateInputBody($input, $allKeys, $requiredKeys)) {
            return $this->respondWithData("Bad Request", 400);
        }

        $UUID = $this->UUIDV4();
        $PDO = $this->pdoConnect($_ENV['DB_PORTAL']);

        $sqlQuery = "INSERT INTO setting_services
                    SET
                        service_id = :service_id, 
                        service_group = :service_group, 
                        service_name = :service_name";

        $stmt = $PDO->prepare($sqlQuery);
        $stmt->bindValue(':service_id', $UUID);
        $stmt->bindValue(':service_group', $input['service_group']);
        $stmt->bindValue(':service_name', $input['service_name']);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            return $this->respondWithData("Created");
        } else {
            return $this->respondWithData("Failed", 400);
        }
    }
}
