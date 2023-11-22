<?php

declare(strict_types=1);

namespace App\Application\Actions\Setting;

use Psr\Http\Message\ResponseInterface as Response;

class TypeGetList extends MainAction
{
    protected function action(): Response
    {
        $PDO = $this->pdoConnect($_ENV['DB_PORTAL']);

        $sqlQuery = "SELECT
            pt.type_id,
            pt.type_name,
            pt.created_at,
            JSON_OBJECT(
                'avatar_path', mi_created.avatar_path,
                'first_name', mi_created.first_name,
                'last_name', mi_created.last_name,
                'code_name', mi_created.code_name
            ) AS created_by,
            pt.updated_at,
            JSON_OBJECT(
                'avatar_path', mi_updated.avatar_path,
                'first_name', mi_updated.first_name,
                'last_name', mi_updated.last_name,
                'code_name', mi_updated.code_name
            ) AS updated_by
            FROM type_project pt
            LEFT JOIN member_info mi_created ON pt.created_by = mi_created.account_id
            LEFT JOIN member_info mi_updated ON pt.updated_by = mi_updated.account_id;";

        $stmt = $PDO->prepare($sqlQuery);
        $stmt->execute();

        if ($stmt->rowCount() === 0) return $this->respondWithData("No data", 404);
        $allData = $stmt->fetchAll();
        return $this->respondWithData($allData);
    }
}
