<?php

declare(strict_types=1);

namespace App\Application\Actions\Customer;

use Psr\Http\Message\ResponseInterface as Response;

class SettingBranchEdit extends MainAction
{
    protected function action(): Response
    {
        $input = $this->getFormData();
        $allKeys = ["branch_id", "branch_status", "branch_type", "branch_name", "branch_code"];
        $requiredKeys = ["branch_id"];

        if (!$this->validateInputBody($input, $allKeys, $requiredKeys)) {
            return $this->respondWithData("Bad Request", 400);
        }

        $PDO = $this->pdoConnect($_ENV['DB_PORTAL']);

        $sqlQuery = "UPDATE setting_branches
                    SET 
                        branch_status = :branch_status, 
                        branch_type = :branch_type, 
                        branch_name = :branch_name, 
                        branch_code = :branch_code 
                    WHERE 
                        branch_id = :branch_id";

        $stmt = $PDO->prepare($sqlQuery);
        $stmt->bindValue(':branch_id', $input['branch_id']);
        $stmt->bindValue(':branch_status', $input['branch_status']);
        $stmt->bindValue(':branch_type', $input['branch_type']);
        $stmt->bindValue(':branch_name', $input['branch_name']);
        $stmt->bindValue(':branch_code', $input['branch_code']);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            return $this->respondWithData("Edited", 200);
        } else {
            return $this->respondWithData("Failed", 400);
        }
    }
}
