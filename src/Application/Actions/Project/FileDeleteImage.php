<?php

declare(strict_types=1);

namespace App\Application\Actions\Project;

use Psr\Http\Message\ResponseInterface as Response;

class FileDeleteImage extends MainAction
{
    protected function action(): Response
    {
        $input = $this->getFormData();
        $allKeys = ["project_id", "file_type", "image_path"];
        $requiredKeys = ["project_id", "file_type", "image_path"];

        if (!$this->validateInputBody($input, $allKeys, $requiredKeys)) {
            return $this->respondWithData("Bad Request", 400);
        }

        $pdo = $this->pdoConnect($_ENV['DB_PORTAL']);

        if ($input['file_type'] === 'main') {
            $this->deleteTheImage($pdo, $input);
        }

        if ($input['file_type'] === 'plan1') {
            $this->deleteImagePlan1($pdo, $input);
        }

        if ($input['file_type'] === 'plan2') {
            $this->deleteImagePlan2($pdo, $input);
        }

        if ($input['file_type'] === 'plan3') {
            $this->deleteImagePlan3($pdo, $input);
        }

        if ($input['file_type'] === 'plan4') {
            $this->deleteTheImage($pdo, $input);
        }

        return $this->respondWithData("Delete Success");
    }

    private function deleteTheImage($pdo, $input)
    {
        $sqlQuery = "DELETE FROM project_file WHERE project_id = :project_id AND file_type = :file_type";
        $stmt = $pdo->prepare($sqlQuery);
        $stmt->bindValue(':project_id', $input['project_id']);
        $stmt->bindValue(':file_type', $input['file_type']);
        $deleteResult = $stmt->execute();
        if (!$deleteResult) return $this->respondWithData("Delete Fail", 404);

        $runDeleteImage = $this->deleteImage($input['image_path']);
        if (!$runDeleteImage) return $this->respondWithData("Delete Image Fail", 404);

        return true;
    }

    private function deleteImagePlan1($pdo, $input)
    {
        $sqlQuery = "DELETE FROM project_file WHERE project_id = :project_id AND file_type = :file_type";
        $stmt = $pdo->prepare($sqlQuery);
        $stmt->bindValue(':project_id', $input['project_id']);
        $stmt->bindValue(':file_type', $input['file_type']);
        $deleteResult = $stmt->execute();
        if (!$deleteResult) return $this->respondWithData("Delete Fail", 404);

        $runDeleteImage = $this->deleteImage($input['image_path']);
        if (!$runDeleteImage) return $this->respondWithData("Delete Image Fail", 404);

        return true;
    }

    private function deleteImagePlan2($pdo, $input)
    {
        $sqlQuery = "DELETE FROM project_file WHERE project_id = :project_id AND file_type = :file_type";
        $stmt = $pdo->prepare($sqlQuery);
        $stmt->bindValue(':project_id', $input['project_id']);
        $stmt->bindValue(':file_type', $input['file_type']);
        $deleteResult = $stmt->execute();
        if (!$deleteResult) return $this->respondWithData("Delete Fail", 404);

        $runDeleteImage = $this->deleteImage($input['image_path']);
        if (!$runDeleteImage) return $this->respondWithData("Delete Image Fail", 404);

        return true;
    }

    private function deleteImagePlan3($pdo, $input)
    {
        $sqlQueryCheck = "SELECT * FROM project_file WHERE project_id = :project_id AND file_type = 'plan4'";
        $stmtCheck = $pdo->prepare($sqlQueryCheck);
        $stmtCheck->bindValue(':project_id', $input['project_id']);
        $stmtCheck->execute();

        $checkData = $stmtCheck->fetchAll();

        if (empty($checkData)) {
            $sqlQuery = "DELETE FROM project_file WHERE project_id = :project_id AND file_type = :file_type";
            $stmt = $pdo->prepare($sqlQuery);
            $stmt->bindValue(':project_id', $input['project_id']);
            $stmt->bindValue(':file_type', $input['file_type']);
            $deleteResult = $stmt->execute();
            if (!$deleteResult) {
                return $this->respondWithData("Delete Fail", 404);
            }

            $runDeleteImage = $this->deleteImage($input['image_path']);
            if (!$runDeleteImage) {
                return $this->respondWithData("Delete Image Fail", 404);
            }

            return true;
        } else {
            $runDeleteImage = $this->deleteImage($input['image_path']);
            if (!$runDeleteImage) {
                return $this->respondWithData("Delete Image Fail", 404);
            }

            $sqlMoveImagePath = "UPDATE project_file SET image_path = :image_path WHERE project_id = :project_id";
            $stmt = $pdo->prepare($sqlMoveImagePath);
            $stmt->bindValue(':project_id', $input['project_id']);
            $stmt->bindValue(':image_path', $checkData[0]['image_path']);
            $updateResult = $stmt->execute();
            if (!$updateResult) {
                return $this->respondWithData("Update Image Path Fail", 404);
            }

            $sqlQuery = "DELETE FROM project_file WHERE project_id = :project_id AND file_type = 'plan4'";
            $stmt = $pdo->prepare($sqlQuery);
            $stmt->bindValue(':project_id', $input['project_id']);
            $stmt->bindValue(':file_type', $input['file_type']);
            $deleteResult = $stmt->execute();
            if (!$deleteResult) {
                return $this->respondWithData("Delete Fail", 404);
            }

            return true;
        }
    }
}
