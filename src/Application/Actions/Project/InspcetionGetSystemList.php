<?php

declare(strict_types=1);

namespace App\Application\Actions\Project;

use Psr\Http\Message\ResponseInterface as Response;

class InspcetionGetSystemList extends MainAction
{
    protected function action(): Response
    {
        $input = $this->getFormData();
        $allKeys = ["project_id", "inspection_id"];
        $requiredKeys = ["project_id", "inspection_id"];

        if (!$this->validateInputBody($input, $allKeys, $requiredKeys)) {
            return $this->respondWithData("Bad Request", 400);
        }

        $pdo = $this->pdoConnect($_ENV['DB_PORTAL']);

        $sqlQuery = "SELECT
                        il.project_id, 
                        il.inspection_id, 
                        il.system_id, 
                        il.system_name, 
                        COUNT(ild.system_id) AS deflect_count,
                        SUM(CASE WHEN ild.deflect_status = 0 THEN 1 ELSE 0 END) AS deflect_status_0_count,
                        SUM(CASE WHEN ild.deflect_status = 1 THEN 1 ELSE 0 END) AS deflect_status_1_count,
                        SUM(CASE WHEN ild.deflect_status IS NULL THEN 1 ELSE 0 END) AS deflect_status_null_count
                    FROM
                        inspection_system il
                    LEFT JOIN
                        inspection_system_deflect ild ON il.system_id = ild.system_id
                    WHERE
                        il.project_id = :project_id AND il.inspection_id = :inspection_id
                    GROUP BY
                        il.system_id, il.project_id, il.inspection_id, il.system_name";

        $stmt = $pdo->prepare($sqlQuery);
        $stmt->bindValue(':project_id', $input['project_id']);
        $stmt->bindValue(':inspection_id', $input['inspection_id']);
        $stmt->execute();

        $allData = $stmt->fetchAll();
        foreach ($allData as &$row) {
            $row['deflect_status_0_count'] = (int)$row['deflect_status_0_count'];
            $row['deflect_status_1_count'] = (int)$row['deflect_status_1_count'];
        }

        return $this->respondWithData($allData);
    }
}
