<?php

declare(strict_types=1);

namespace App\Application\Actions\Customer;

use Psr\Http\Message\ResponseInterface as Response;

class SettingBranchActive extends MainAction
{
    protected function action(): Response
    {
        $input = $this->getFormData();
        $allKeys = ["branch_id"];
        $requiredKeys = ["branch_id"];

        if (!$this->validateInputBody($input, $allKeys, $requiredKeys)) {
            return $this->respondWithData("Bad Request", 400);
        }

        $PDO = $this->pdoConnect($_ENV['DB_PORTAL']);

        $sqlQuery = "UPDATE setting_branches
                    SET 
                        branch_status = :branch_status 
                    WHERE 
                        branch_id = :branch_id";

        $stmt = $PDO->prepare($sqlQuery);
        $stmt->bindValue(':branch_id', $input['branch_id']);
        $stmt->bindValue(':branch_status', '1');
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            return $this->respondWithData("OK", 200);
        } else {
            return $this->respondWithData("Failed", 400);
        }
    }
}
