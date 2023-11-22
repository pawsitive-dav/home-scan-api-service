<?php

declare(strict_types=1);

namespace App\Application\Actions\Project;

use Psr\Http\Message\ResponseInterface as Response;

class InspcetionGetList extends MainAction
{
    protected function action(): Response
    {
        $input = $this->getFormData();
        $allKeys = ["project_id"];
        $requiredKeys = ["project_id"];

        if (!$this->validateInputBody($input, $allKeys, $requiredKeys)) {
            return $this->respondWithData("Bad Request", 400);
        }

        $pdo = $this->pdoConnect($_ENV['DB_PORTAL']);

        $sqlQuery = "SELECT 
                    ide.id,
                    ide.inspection_id,
                    ide.working_date,
                    ide.inspection_no,
                    ide.created_at,
                    rl.report_id,
                    rl.report_status,
                    JSON_OBJECT(
                        'avatar_path', mi_created_by.avatar_path,
                        'first_name', mi_created_by.first_name,
                        'last_name', mi_created_by.last_name,
                        'code_name', mi_created_by.code_name
                    ) AS created_by
                    FROM inspection_detail ide
                    INNER JOIN member_info mi_created_by ON ide.created_by = mi_created_by.account_id 
                    LEFT JOIN report_list rl ON ide.project_id = rl.project_id AND ide.inspection_id = rl.inspection_id
                    WHERE ide.project_id = :project_id";

        $stmt = $pdo->prepare($sqlQuery);
        $stmt->bindValue('project_id', $input['project_id']);
        $stmt->execute();

        $allData = $stmt->fetchAll();

        return $this->respondWithData($allData);
    }
}
