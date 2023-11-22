<?php

declare(strict_types=1);

namespace App\Application\Actions\Project;

use Psr\Http\Message\ResponseInterface as Response;

class FileUploadImage extends MainAction
{
    protected function action(): Response
    {
        $input = $this->getFormData();
        $allKeys = ["project_id", "file_type", "image"];
        $requiredKeys = ["project_id", "file_type", "image"];

        if (!$this->validateInputBody($input, $allKeys, $requiredKeys)) {
            return $this->respondWithData("Bad Request", 400);
        }

        $runUploadImage = $this->uploadImage($input['image']);
        if (!$runUploadImage) return $this->respondWithData("Upload image Fail", 404);

        $pdo = $this->pdoConnect($_ENV['DB_PORTAL']);

        $sqlProjectFile = "INSERT INTO project_file
                        SET
                            project_id = :project_id, 
                            file_type = :file_type, 
                            image_path = :image_path, 
                            image_size = :image_size";

        $stmtProjectFile = $pdo->prepare($sqlProjectFile);
        $stmtProjectFile->bindValue(':project_id', $input['project_id']);
        $stmtProjectFile->bindValue(':file_type', $input['file_type']);
        $stmtProjectFile->bindValue(':image_path', $runUploadImage['file_path']);
        $stmtProjectFile->bindValue(':image_size', (int)$runUploadImage['file_size']);
        $createProjectFile = $stmtProjectFile->execute();
        if (!$createProjectFile) return $this->respondWithData('Upload project file fail', 404);

        return $this->respondWithData('Upload success');
    }
}
