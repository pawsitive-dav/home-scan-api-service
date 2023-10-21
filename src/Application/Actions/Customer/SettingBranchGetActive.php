<?php

declare(strict_types=1);

namespace App\Application\Actions\Customer;

use Psr\Http\Message\ResponseInterface as Response;

class SettingBranchGetActive extends MainAction
{
    protected function action(): Response
    {
        $PDO = $this->pdoConnect($_ENV['DB_PORTAL']);
        $sqlQuery = "SELECT 
                    branch_id, branch_status, branch_type, branch_name, branch_code 
                    FROM setting_branches WHERE branch_status = '1'";

        $stmt = $PDO->prepare($sqlQuery);
        $stmt->execute();

        if ($stmt->rowCount() == 0) {
            return $this->respondWithData("Fail", 404);
        }

        $allData = $stmt->fetchAll();
        return $this->respondWithData($allData);
    }
}
