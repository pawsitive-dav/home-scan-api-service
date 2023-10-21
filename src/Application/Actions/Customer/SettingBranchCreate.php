<?php

declare(strict_types=1);

namespace App\Application\Actions\Customer;

use Psr\Http\Message\ResponseInterface as Response;

class SettingBranchCreate extends MainAction
{
    protected function action(): Response
    {
        $input = $this->getFormData();
        $allKeys = ["branch_status", "branch_type", "branch_name", "branch_code"];
        $requiredKeys = ["branch_status", "branch_type", "branch_name", "branch_code"];

        if (!$this->validateInputBody($input, $allKeys, $requiredKeys)) {
            return $this->respondWithData("Bad Request", 400);
        }

        $UUID = $this->UUIDV4();
        $PDO = $this->pdoConnect($_ENV['DB_PORTAL']);

        $sqlQuery = "INSERT INTO setting_branches
                    SET
                        branch_id = :branch_id, 
                        branch_status = :branch_status, 
                        branch_type = :branch_type, 
                        branch_name = :branch_name, 
                        branch_code = :branch_code";

        $stmt = $PDO->prepare($sqlQuery);
        $stmt->bindValue(':branch_id', $UUID);
        $stmt->bindValue(':branch_status', $input['branch_status']);
        $stmt->bindValue(':branch_type', $input['branch_type']);
        $stmt->bindValue(':branch_name', $input['branch_name']);
        $stmt->bindValue(':branch_code', $input['branch_code']);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            return $this->respondWithData("Created");
        } else {
            return $this->respondWithData("Failed", 400);
        }
    }
}
