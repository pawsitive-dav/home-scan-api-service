<?php

declare(strict_types=1);

namespace App\Application\Actions\Project;

use Psr\Http\Message\ResponseInterface as Response;

class ProjectDelete extends MainAction
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
        $projectId = $input['project_id'];

        $runDeleteProjectDetail = $this->deleteProjectDetail($pdo, $projectId);
        if (!$runDeleteProjectDetail) {
            return $this->respondWithData("Delete project detail failed", 404);
        }

        $runDeleteTypeDetail = $this->deleteTypeDetail($pdo, $projectId);
        if (!$runDeleteTypeDetail) {
            return $this->respondWithData("Delete type detail failed", 404);
        }

        $runDeleteCustomerDetail = $this->deleteCustomerDetail($pdo, $projectId);
        if (!$runDeleteCustomerDetail) {
            return $this->respondWithData("Delete customer detail failed", 404);
        }

        $runDeleteProjectCoordinator = $this->deleteProjectCoordinator($pdo, $projectId);
        if (!$runDeleteProjectCoordinator) {
            return $this->respondWithData("Delete project coordinator failed", 404);
        }

        $runDeleteProjectTeams = $this->deleteProjectTeams($pdo, $projectId);
        if (!$runDeleteProjectTeams) {
            return $this->respondWithData("Delete project teams failed", 404);
        }

        $runDeleteProjectTeamsChecker = $this->deleteProjectTeamsChecker($pdo, $projectId);
        if (!$runDeleteProjectTeamsChecker) {
            return $this->respondWithData("Delete project teams checker failed", 404);
        }

        $runDeleteProjectFile = $this->deleteProjectFile($pdo, $projectId);
        if (!$runDeleteProjectFile) {
            return $this->respondWithData("Delete project file failed", 404);
        }

        return $this->respondWithData("Delete project successfully");
    }

    private function deleteProjectDetail($pdo, $projectId)
    {
        $sqlQuery = "DELETE FROM project_detail WHERE project_id = :project_id";
        $stmt = $pdo->prepare($sqlQuery);
        $stmt->bindValue(':project_id', $projectId);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    private function deleteTypeDetail($pdo, $projectId)
    {
        $sqlQuery = "DELETE FROM project_type_detail WHERE project_id = :project_id";
        $stmt = $pdo->prepare($sqlQuery);
        $stmt->bindValue(':project_id', $projectId);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    private function deleteCustomerDetail($pdo, $projectId)
    {
        $sqlQuery = "DELETE FROM project_customer WHERE project_id = :project_id";
        $stmt = $pdo->prepare($sqlQuery);
        $stmt->bindValue(':project_id', $projectId);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    private function deleteProjectCoordinator($pdo, $projectId)
    {
        $sqlQuery = "DELETE FROM project_coordinator WHERE project_id = :project_id";
        $stmt = $pdo->prepare($sqlQuery);
        $stmt->bindValue(':project_id', $projectId);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    private function deleteProjectTeams($pdo, $projectId)
    {
        $sqlQuery = "DELETE FROM project_teams WHERE project_id = :project_id";
        $stmt = $pdo->prepare($sqlQuery);
        $stmt->bindValue(':project_id', $projectId);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    private function deleteProjectTeamsChecker($pdo, $projectId)
    {
        $sqlQuery = "SELECT * FROM project_teams_checker WHERE project_id = :project_id";
        $stmt = $pdo->prepare($sqlQuery);
        $stmt->bindValue(':project_id', $projectId);
        $stmt->execute();

        if ($stmt->rowCount() === 0) {
            return true;
        } else {
            $sqlQuery = "DELETE FROM project_teams_checker WHERE project_id = :project_id";
            $stmt = $pdo->prepare($sqlQuery);
            $stmt->bindValue(':project_id', $projectId);
            $stmt->execute();
            return $stmt->rowCount() > 0;
        }
    }

    private function deleteProjectFile($pdo, $projectId)
    {
        $sqlQuery = "SELECT * FROM project_file WHERE project_id = :project_id";
        $stmt = $pdo->prepare($sqlQuery);
        $stmt->bindValue(':project_id', $projectId);
        $stmt->execute();
        $getImagePath = null;

        if ($stmt->rowCount() === 0) {
            return true;
        } else {
            $getImagePath = $stmt->fetchAll();

            foreach ($getImagePath as $row) {
                $runDeleteImage = $this->deleteImage($row['image_path']);
                if (!$runDeleteImage) {
                    return false;
                }
            }

            $sqlQuery = "DELETE FROM project_file WHERE project_id = :project_id";
            $stmt = $pdo->prepare($sqlQuery);
            $stmt->bindValue(':project_id', $projectId);
            $stmt->execute();
            return $stmt->rowCount() > 0;
        }
    }
}
