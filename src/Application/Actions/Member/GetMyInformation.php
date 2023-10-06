<?php

declare(strict_types=1);

namespace App\Application\Actions\Member;

use Psr\Http\Message\ResponseInterface as Response;

class GetMyInformation extends MainAction
{
    protected function action(): Response
    {
        $account_id = $this->request->getAttribute('tokenInfo')->data;

        $pdo = $this->pdoConnect($_ENV['DB_MEMBER']);
        $sqlQuery = "SELECT * FROM member_info WHERE account_id = :account_id ";

        $stmt = $pdo->prepare($sqlQuery);
        $stmt->bindValue(":account_id", $account_id);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            $allData = $stmt->fetchAll()[0];
            $object = [
                "account_id" => $allData['account_id'],
                "avatar_path" => $allData['avatar_path'],
                "first_name" => $allData['first_name'],
                "last_name" => $allData['last_name'],
                "code_name" => $allData['code_name'],
                "member_role" => $allData['member_role'],
            ];
            return $this->respondWithData($object);
        } else {
            return $this->respondWithData("Failed", 400);
        }
    }
}
