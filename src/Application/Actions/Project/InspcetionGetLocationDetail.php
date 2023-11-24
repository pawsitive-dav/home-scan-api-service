<?php

declare(strict_types=1);

namespace App\Application\Actions\Project;

use Psr\Http\Message\ResponseInterface as Response;

class InspcetionGetLocationDetail extends MainAction
{
    protected function action(): Response
    {
        $input = $this->getFormData();
        $allKeys = ["location_id"];
        $requiredKeys = ["location_id"];

        if (!$this->validateInputBody($input, $allKeys, $requiredKeys)) {
            return $this->respondWithData("Bad Request", 400);
        }

        $pdo = $this->pdoConnect($_ENV['DB_PORTAL']);

        $sqlQuery = "SELECT
                        il.project_id, 
                        il.inspection_id, 
                        il.location_id, 
                        il.location_name, 
                        pd.project_name,
                        id.inspection_no,
                        rl.report_status
                    FROM inspection_location il
                    LEFT JOIN project_detail pd ON il.project_id = pd.project_id
                    LEFT JOIN inspection_detail id ON il.inspection_id = id.inspection_id
                    LEFT JOIN report_list rl ON il.inspection_id = rl.inspection_id
                    WHERE il.location_id = :location_id";

        $stmt = $pdo->prepare($sqlQuery);
        $stmt->bindValue(':location_id', $input['location_id']);
        $stmt->execute();

        $allData = $stmt->fetchAll();

        if (empty($allData)) {
            return $this->respondWithData("No data found for location_id: {$input['location_id']}", 404);
        }

        return $this->respondWithData($allData[0]);
    }
}
