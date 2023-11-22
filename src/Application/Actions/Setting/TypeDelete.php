<?php

declare(strict_types=1);

namespace App\Application\Actions\Setting;

use Psr\Http\Message\ResponseInterface as Response;

class TypeDelete extends MainAction
{
    protected function action(): Response
    {
        $input = $this->getFormData();
        $allKeys = ["type_id"];
        $requiredKeys = ["type_id"];

        if (!$this->validateInputBody($input, $allKeys, $requiredKeys)) {
            return $this->respondWithData("Bad Request", 400);
        }

        $PDO = $this->pdoConnect($_ENV['DB_PORTAL']);

        $sqlQuery = "DELETE FROM type_project WHERE type_id = :type_id";
        $stmt = $PDO->prepare($sqlQuery);
        $stmt->bindValue(':type_id', $input['type_id']);
        $stmt->execute();

        if ($stmt->rowCount() == 0) {
            return $this->respondWithData("Fail", 404);
        } else {
            return $this->respondWithData("Success");
        }
    }
}
