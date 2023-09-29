<?php

declare(strict_types=1);

namespace App\Application\Actions\Member;

use Psr\Http\Message\ResponseInterface as Response;

class GetMemberInfo extends MemberAction
{
    protected function action(): Response
    {
        $pdo = $this->pdoConnect($_ENV['DB_MEMBER']);
        $sqlQuery = "SELECT * FROM member_info";
        $stmt = $pdo->prepare($sqlQuery);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            $allData = $stmt->fetchAll();
            return $this->respondWithData($allData);
        } else {
            return $this->respondWithData("Failed", 400);
        }
    }
}
