<?php

declare(strict_types=1);

namespace App\Application\Actions\Customer;

use Psr\Http\Message\ResponseInterface as Response;

class SettingBranchDelete extends MainAction
{
    protected function action(): Response
    {
        $input = $this->getFormData();
        $allKeys = ["branch_id"];
        $requiredKeys = ["branch_id"];

        if (!$this->validateInputBody($input, $allKeys, $requiredKeys)) {
            return $this->respondWithData("Bad Request", 400);
        }

        $pdo = $this->pdoConnect($_ENV['DB_PORTAL']);

        $sqlQuery = "DELETE FROM setting_branches WHERE branch_id = :branch_id";
        $stmt = $pdo->prepare($sqlQuery);
        $stmt->bindValue(':branch_id', $input['branch_id']);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            return $this->respondWithData("Deleted", 200);
        } else {
            return $this->respondWithData("Failed", 400);
        }
    }
}
