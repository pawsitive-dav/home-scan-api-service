<?php

declare(strict_types=1);

namespace App\Application\Actions\Role;

use Psr\Http\Message\ResponseInterface as Response;

class GetRole extends MainAction
{
    protected function action(): Response
    {
        $pdo = $this->pdoConnect($_ENV['DB_MEMBER']);

        $sqlQuery = "SELECT role_name, role_level FROM app_role";

        $stmt = $pdo->prepare($sqlQuery);
        $stmt->execute();

        if ($stmt->rowCount() == 0) {
            return $this->respondWithData("Fail", 404);
        } else {
            $allData = $stmt->fetchAll();
        }
        return $this->respondWithData($allData);
    }
}
