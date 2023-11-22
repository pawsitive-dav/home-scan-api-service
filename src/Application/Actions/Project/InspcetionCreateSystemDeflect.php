<?php

declare(strict_types=1);

namespace App\Application\Actions\Project;

use Psr\Http\Message\ResponseInterface as Response;

class InspcetionCreateSystemDeflect extends MainAction
{
    protected function action(): Response
    {
        $input = $this->getFormData();

        $allKeys = ["project_id", "inspection_id", "system_id", "image_id"];
        $requiredKeys = ["project_id", "inspection_id", "system_id", "image_id"];

        if (!$this->validateInputBody($input, $allKeys, $requiredKeys)) {
            return $this->respondWithData("Bad Request", 400);
        }

        date_default_timezone_set("Asia/Bangkok");
        $date_now = date('Y-m-d H:i:s');
        $pdo = $this->pdoConnect($_ENV['DB_PORTAL']);
        $account_id = $this->request->getAttribute('tokenInfo')->data;

        $allList = explode(",", $input['image_id']);

        $sqlQuery = "INSERT INTO inspection_system_deflect
                SET
                    project_id = :project_id, 
                    inspection_id = :inspection_id, 
                    system_id = :system_id, 
                    image_id = :image_id,
                    created_at = :created_at,
                    created_by = :created_by";

        $stmt = $pdo->prepare($sqlQuery);

        foreach ($allList as $image_id) {
            $stmt->bindValue(':project_id', $input['project_id']);
            $stmt->bindValue(':inspection_id', $input['inspection_id']);
            $stmt->bindValue(':system_id', $input['system_id']);
            $stmt->bindValue(':image_id', $image_id);
            $stmt->bindValue(':created_at', $date_now);
            $stmt->bindValue(':created_by', $account_id);

            $result = $stmt->execute();

            if (!$result) {
                return $this->respondWithData("Fail", 404);
            }
        }

        return $this->respondWithData("Success");
    }
}
