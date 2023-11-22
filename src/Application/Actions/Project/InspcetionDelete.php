<?php

declare(strict_types=1);

namespace App\Application\Actions\Project;

use Psr\Http\Message\ResponseInterface as Response;

class InspcetionDelete extends MainAction
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

        // inspection_location_deflect
        $sqlQuery = "DELETE FROM inspection_location_deflect 
                    WHERE project_id = :project_id AND inspection_id = :inspection_id";

        $stmt = $pdo->prepare($sqlQuery);
        $stmt->bindValue(':project_id', $input['project_id']);
        $stmt->bindValue(':inspection_id', $input['inspection_id']);
        $deleteInspectionLocationDeflectResult = $stmt->execute();

        if (empty($deleteInspectionLocationDeflectResult)) return $this->respondWithData("Delete Location Deflect Fail", 404);

        // inspection_location
        $sqlQuery = "DELETE FROM inspection_location 
                    WHERE project_id = :project_id AND inspection_id = :inspection_id";

        $stmt = $pdo->prepare($sqlQuery);
        $stmt->bindValue(':project_id', $input['project_id']);
        $stmt->bindValue(':inspection_id', $input['inspection_id']);
        $deleteInspectionLocationResult = $stmt->execute();

        if (empty($deleteInspectionLocationResult)) return $this->respondWithData("Delete Location Fail", 404);

        // inspection_system_deflect
        $sqlQuery = "DELETE FROM inspection_system_deflect 
                    WHERE project_id = :project_id AND inspection_id = :inspection_id";

        $stmt = $pdo->prepare($sqlQuery);
        $stmt->bindValue(':project_id', $input['project_id']);
        $stmt->bindValue(':inspection_id', $input['inspection_id']);
        $deleteInspectionSystemDeflectResult = $stmt->execute();

        if (empty($deleteInspectionSystemDeflectResult)) return $this->respondWithData("Delete System Deflect Fail", 404);

        // inspection_system
        $sqlQuery = "DELETE FROM inspection_system 
                    WHERE project_id = :project_id AND inspection_id = :inspection_id";

        $stmt = $pdo->prepare($sqlQuery);
        $stmt->bindValue(':project_id', $input['project_id']);
        $stmt->bindValue(':inspection_id', $input['inspection_id']);
        $deleteInspectionSystemResult = $stmt->execute();

        if (empty($deleteInspectionSystemResult)) return $this->respondWithData("Delete System Fail", 404);

        // Delete inspection_detail
        $sqlQuery = "DELETE FROM inspection_detail 
                    WHERE project_id = :project_id AND inspection_id = :inspection_id";

        $stmt = $pdo->prepare($sqlQuery);
        $stmt->bindValue(':project_id', $input['project_id']);
        $stmt->bindValue(':inspection_id', $input['inspection_id']);
        $deleteInspectionResult = $stmt->execute();

        if (empty($deleteInspectionResult)) return $this->respondWithData("Delete Inspection Fail", 404);

        $sqlQueryInspection = "SELECT * FROM inspection_detail WHERE project_id = :project_id";
        $stmtInspection = $pdo->prepare($sqlQueryInspection);
        $stmtInspection->bindValue(':project_id', $input['project_id']);
        $stmtInspection->execute();
        $hasInspectionData = $stmtInspection->rowCount() == 0;

        date_default_timezone_set("Asia/Bangkok");
        $DATE_NOW = date('Y-m-d H:i:s');

        if ($hasInspectionData) {
            $sqlQueryUpdateStatus = "UPDATE project_detail
                            SET
                                project_status = 'to-do',  
                                status_update_at = :status_update_at
                            WHERE
                                project_id = :project_id";

            $stmtUpdateStatus = $pdo->prepare($sqlQueryUpdateStatus);
            $stmtUpdateStatus->bindValue(':project_id', $input['project_id']);
            $stmtUpdateStatus->bindValue(':status_update_at', $DATE_NOW);
            $stmtUpdateStatus->execute();
        } else {
            $sqlQueryUpdateStatus = "UPDATE project_detail
                            SET
                                project_status = 'done',  
                                status_update_at = :status_update_at
                            WHERE
                                project_id = :project_id";

            $stmtUpdateStatus = $pdo->prepare($sqlQueryUpdateStatus);
            $stmtUpdateStatus->bindValue(':project_id', $input['project_id']);
            $stmtUpdateStatus->bindValue(':status_update_at', $DATE_NOW);
            $stmtUpdateStatus->execute();
        }

        return $this->respondWithData("Delete Inspection Success");
    }
}
