<?php

declare(strict_types=1);

namespace App\Application\Actions\Customer;

use Psr\Http\Message\ResponseInterface as Response;

class SettingServiceDelete extends MainAction
{
    protected function action(): Response
    {
        $input = $this->getFormData();
        $allKeys = ["service_id"];
        $requiredKeys = ["service_id"];

        if (!$this->validateInputBody($input, $allKeys, $requiredKeys)) {
            return $this->respondWithData("Bad Request", 400);
        }

        $pdo = $this->pdoConnect($_ENV['DB_PORTAL']);

        $sqlQuery = "DELETE FROM setting_services WHERE service_id = :service_id";
        $stmt = $pdo->prepare($sqlQuery);
        $stmt->bindValue(':service_id', $input['service_id']);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            return $this->respondWithData("Deleted", 200);
        } else {
            return $this->respondWithData("Failed", 400);
        }
    }
}
