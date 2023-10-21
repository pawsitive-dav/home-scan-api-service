<?php

declare(strict_types=1);

namespace App\Application\Actions\Customer;

use Psr\Http\Message\ResponseInterface as Response;

class ExternalBacklogGetList extends MainAction
{
    protected function action(): Response
    {
        $PDO = $this->pdoConnect($_ENV['DB_PORTAL']);
        $sqlQuery = "SELECT * FROM external_backlog";

        $stmt = $PDO->prepare($sqlQuery);
        $stmt->execute();

        if ($stmt->rowCount() == 0) {
            return $this->respondWithData("Fail", 404);
        }

        $allData = $stmt->fetchAll();
        return $this->respondWithData($allData);
    }
}
