<?php

declare(strict_types=1);

namespace App\Application\Actions\Setting;

use Psr\Http\Message\ResponseInterface as Response;

class SystemDelete extends MainAction
{
    protected function action(): Response
    {
        $input = $this->getFormData();
        $allKeys = ["system_id"];
        $requiredKeys = ["system_id"];

        if (!$this->validateInputBody($input, $allKeys, $requiredKeys)) {
            return $this->respondWithData("Bad Request", 400);
        }

        $PDO = $this->pdoConnect($_ENV['DB_PORTAL']);

        $sqlQuery = "DELETE FROM type_system WHERE system_id = :system_id";
        $stmt = $PDO->prepare($sqlQuery);
        $stmt->bindValue(':system_id', $input['system_id']);
        $stmt->execute();

        if ($stmt->rowCount() == 0) {
            return $this->respondWithData("Fail", 404);
        } else {
            return $this->respondWithData("Success");
        }
    }
}
