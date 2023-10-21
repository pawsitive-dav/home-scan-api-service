<?php

declare(strict_types=1);

namespace App\Application\Actions\Member;

use Psr\Http\Message\ResponseInterface as Response;

class GetMemberInfoBy extends MainAction
{
    protected function action(): Response
    {
        $account_id = $this->resolveArg('id');

        $pdo = $this->pdoConnect($_ENV['DB_PORTAL']);
        $sqlQuery = "SELECT * FROM member_info WHERE account_id = :account_id ";

        $stmt = $pdo->prepare($sqlQuery);
        $stmt->bindValue(":account_id", $account_id);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            $allData = $stmt->fetchAll()[0];
            return $this->respondWithData($allData);
        } else {
            return $this->respondWithData("Failed", 400);
        }
    }
}
