<?php

declare(strict_types=1);

namespace App\Application\Actions\Project;

use Psr\Http\Message\ResponseInterface as Response;

class InspcetionGetDetail extends MainAction
{
    protected function action(): Response
    {
        $input = $this->getFormData();
        $allKeys = ["inspection_id"];
        $requiredKeys = ["inspection_id"];

        if (!$this->validateInputBody($input, $allKeys, $requiredKeys)) {
            return $this->respondWithData("Bad Request", 400);
        }

        $pdo = $this->pdoConnect($_ENV['DB_PORTAL']);

        $sqlQuery = "SELECT 
                    ide.project_id, 
                    pd.project_name, 
                    ide.inspection_id, 
                    ide.working_date, 
                    ide.inspection_no, 
                    ide.created_at,
                    rl.report_status,
                    rl.report_id 
                    FROM inspection_detail ide
                    INNER JOIN project_detail pd ON ide.project_id = pd.project_id 
                    LEFT JOIN report_list rl ON ide.inspection_id = rl.inspection_id 
                    WHERE ide.inspection_id = :inspection_id";

        $stmt = $pdo->prepare($sqlQuery);
        $stmt->bindValue('inspection_id', $input['inspection_id']);
        $stmt->execute();

        $allData = $stmt->fetchAll();

        return $this->respondWithData($allData[0]);
    }
}
