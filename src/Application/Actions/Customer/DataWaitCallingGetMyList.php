<?php

declare(strict_types=1);

namespace App\Application\Actions\Customer;

use Psr\Http\Message\ResponseInterface as Response;

class DataWaitCallingGetMyList extends MainAction
{
    protected function action(): Response
    {
        $account_id = $this->resolveArg('id');

        $PDO = $this->pdoConnect($_ENV['DB_PORTAL']);
        $sqlQuery = "SELECT * FROM data_wait_calling WHERE assignee = :assignee ";

        $stmt = $PDO->prepare($sqlQuery);
        $stmt->bindValue(":assignee", $account_id);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            $allData = $stmt->fetchAll();
            return $this->respondWithData($allData);
        } else {
            return $this->respondWithData("Failed", 400);
        }
    }
}
