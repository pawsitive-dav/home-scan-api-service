<?php

declare(strict_types=1);

namespace App\Application\Actions\Project;

use Psr\Http\Message\ResponseInterface as Response;

class InspcetionGetSystemDetail extends MainAction
{
    protected function action(): Response
    {
        $input = $this->getFormData();
        $allKeys = ["system_id"];
        $requiredKeys = ["system_id"];

        if (!$this->validateInputBody($input, $allKeys, $requiredKeys)) {
            return $this->respondWithData("Bad Request", 400);
        }

        $pdo = $this->pdoConnect($_ENV['DB_PORTAL']);

        $sqlQuery = "SELECT
                        ist.project_id, 
                        ist.inspection_id, 
                        ist.system_id, 
                        ist.system_name, 
                        pd.project_name,
                        id.inspection_no,
                        rl.report_status
                    FROM inspection_system ist
                    LEFT JOIN project_detail pd ON ist.project_id = pd.project_id
                    LEFT JOIN inspection_detail id ON ist.inspection_id = id.inspection_id
                    LEFT JOIN report_list rl ON ist.inspection_id = rl.inspection_id
                    WHERE ist.system_id = :system_id";

        $stmt = $pdo->prepare($sqlQuery);
        $stmt->bindValue(':system_id', $input['system_id']);
        $stmt->execute();

        $allData = $stmt->fetchAll();

        if (empty($allData)) {
            return $this->respondWithData("No data found for system_id: {$input['system_id']}", 404);
        }

        return $this->respondWithData($allData[0]);
    }
}
