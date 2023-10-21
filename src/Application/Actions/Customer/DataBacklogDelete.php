<?php

declare(strict_types=1);

namespace App\Application\Actions\Customer;

use Psr\Http\Message\ResponseInterface as Response;

class DataBacklogDelete extends MainAction
{
    protected function action(): Response
    {
        $input = $this->getFormData();
        $allKeys = ["data_id"];
        $requiredKeys = ["data_id"];

        if (!$this->validateInputBody($input, $allKeys, $requiredKeys)) {
            return $this->respondWithData("Bad Request", 400);
        }

        $pdo = $this->pdoConnect($_ENV['DB_PORTAL']);

        $sqlQuery = "DELETE FROM data_backlog WHERE data_id = :data_id";
        $stmt = $pdo->prepare($sqlQuery);
        $stmt->bindValue(':data_id', $input['data_id']);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            return $this->respondWithData("Deleted", 200);
        } else {
            return $this->respondWithData("Failed", 400);
        }
    }
}
