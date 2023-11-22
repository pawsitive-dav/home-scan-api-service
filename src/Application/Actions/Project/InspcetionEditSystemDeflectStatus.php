<?php

declare(strict_types=1);

namespace App\Application\Actions\Project;

use Psr\Http\Message\ResponseInterface as Response;

class InspcetionEditSystemDeflectStatus extends MainAction
{
    protected function action(): Response
    {
        $input = $this->getFormData();
        $allKeys = ["project_id", "inspection_id", "system_id", "image_id", "deflect_status"];
        $requiredKeys = ["project_id", "inspection_id", "system_id", "image_id"];

        if (!$this->validateInputBody($input, $allKeys, $requiredKeys)) {
            return $this->respondWithData("Bad Request", 400);
        }

        $pdo = $this->pdoConnect($_ENV['DB_PORTAL']);
        date_default_timezone_set("Asia/Bangkok");
        $date_now = date('Y-m-d H:i:s');
        $account_id = $this->request->getAttribute('tokenInfo')->data;

        $sqlQuery = "UPDATE inspection_system_deflect
                    SET
                        deflect_status = :deflect_status,
                        update_status_at = :update_status_at,
                        update_status_by = :update_status_by
                    WHERE
                        project_id = :project_id
                        AND inspection_id = :inspection_id
                        AND system_id = :system_id
                        AND image_id = :image_id";

        $stmt = $pdo->prepare($sqlQuery);
        $stmt->bindValue(':deflect_status', $input['deflect_status']);
        $stmt->bindValue(':update_status_at', $date_now);
        $stmt->bindValue(':update_status_by', $account_id);
        $stmt->bindValue(':project_id', $input['project_id']);
        $stmt->bindValue(':inspection_id', $input['inspection_id']);
        $stmt->bindValue(':system_id', $input['system_id']);
        $stmt->bindValue(':image_id', $input['image_id']);
        $result = $stmt->execute();

        if (empty($result)) return $this->respondWithData("Update Status Fail", 404);

        return $this->respondWithData("Update Status Successfuly");
    }
}
