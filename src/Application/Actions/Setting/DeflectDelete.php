<?php

declare(strict_types=1);

namespace App\Application\Actions\Setting;

use Psr\Http\Message\ResponseInterface as Response;

class DeflectDelete extends MainAction
{
    protected function action(): Response
    {
        $input = $this->getFormData();
        $allKeys = ["deflect_id"];
        $requiredKeys = ["deflect_id"];

        if (!$this->validateInputBody($input, $allKeys, $requiredKeys)) {
            return $this->respondWithData("Bad Request", 400);
        }

        $PDO = $this->pdoConnect($_ENV['DB_PORTAL']);

        $sqlQuery = "DELETE FROM type_deflect WHERE deflect_id = :deflect_id";
        $stmt = $PDO->prepare($sqlQuery);
        $stmt->bindValue(':deflect_id', $input['deflect_id']);
        $stmt->execute();

        if ($stmt->rowCount() == 0) {
            return $this->respondWithData("Fail", 404);
        } else {
            return $this->respondWithData("Success");
        }
    }
}
