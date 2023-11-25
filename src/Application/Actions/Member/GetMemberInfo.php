<?php

declare(strict_types=1);

namespace App\Application\Actions\Member;

use Psr\Http\Message\ResponseInterface as Response;

class GetMemberInfo extends MainAction
{
    protected function action(): Response
    {
        $pdo = $this->pdoConnect($_ENV['DB_PORTAL']);

        $sqlQuery = "SELECT account.account_id, account.external_id, account.username, 
             account.approval, account.account_status, account.created_by, account.created_at, 
             account.last_login, account.reset_password_code,
             member_info.avatar_path, member_info.first_name, member_info.last_name, 
             member_info.code_name, member_info.member_role 
             FROM account 
             INNER JOIN member_info ON account.account_id = member_info.account_id 
             WHERE account.approval <> 0";

        $stmt = $pdo->prepare($sqlQuery);
        $stmt->execute();

        $allData = $stmt->fetchAll();
        return $this->respondWithData($allData);
    }
}
