<?php

declare(strict_types=1);

namespace App\Application\Actions\Member;

use Psr\Http\Message\ResponseInterface as Response;

class GetMemberByRole extends MainAction
{
    protected function action(): Response
    {
        $role = $this->resolveArg('id');

        $PDO = $this->pdoConnect($_ENV['DB_PORTAL']);

        $sqlQuery = "SELECT account.account_id,
             member_info.avatar_path, member_info.first_name, member_info.last_name, 
             member_info.code_name, member_info.member_role 
             FROM account 
             INNER JOIN member_info ON account.account_id = member_info.account_id 
             WHERE account.approval <> 0 AND member_info.member_role = :role";

        $stmt = $PDO->prepare($sqlQuery);
        $stmt->bindValue(':role', $role, \PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() == 0) {
            return $this->respondWithData("Fail", 404);
        } else {
            $allData = $stmt->fetchAll();
        }
        return $this->respondWithData($allData);
    }
}
