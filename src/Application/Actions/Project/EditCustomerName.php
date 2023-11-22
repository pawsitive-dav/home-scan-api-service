<?php

declare(strict_types=1);

namespace App\Application\Actions\Project;

use Psr\Http\Message\ResponseInterface as Response;

class EditCustomerName extends MainAction
{
    protected function action(): Response
    {
        $input = $this->getFormData();
        $allKeys = ["project_id", "customer_name"];
        $requiredKeys = ["project_id", "customer_name"];

        if (!$this->validateInputBody($input, $allKeys, $requiredKeys)) {
            return $this->respondWithData("Bad Request", 400);
        }

        $pdo = $this->pdoConnect($_ENV['DB_PORTAL']);

        $sqlQuery = "UPDATE project_customer
                     SET 
                        customer_name = :customer_name
                     WHERE 
                        project_id = :project_id";

        $stmt = $pdo->prepare($sqlQuery);
        $stmt->bindValue(':project_id', $input['project_id']);
        $stmt->bindValue(':customer_name', $input['customer_name']);

        if (!$stmt->execute()) {
            return $this->respondWithData("Failed to edit", 404);
        }

        return $this->respondWithData("Edit Successfully.", 200);
    }
}
