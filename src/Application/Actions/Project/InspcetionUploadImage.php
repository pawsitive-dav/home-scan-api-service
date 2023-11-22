<?php

declare(strict_types=1);

namespace App\Application\Actions\Project;

use Psr\Http\Message\ResponseInterface as Response;

class InspcetionUploadImage extends MainAction
{
    protected function action(): Response
    {
        $input = $this->getFormData();

        $allKeys = ["project_id", "inspection_id", "image_name", "image"];
        $requiredKeys = ["project_id", "inspection_id", "image"];

        if (!$this->validateInputBody($input, $allKeys, $requiredKeys)) {
            return $this->respondWithData("Bad Request", 400);
        }

        $runUploadImage = $this->uploadImage($input['image']);
        if (!$runUploadImage) return $this->respondWithData("Upload image Fail", 404);

        $pdo = $this->pdoConnect($_ENV['DB_PORTAL']);

        date_default_timezone_set("Asia/Bangkok");
        $date_now = date('Y-m-d H:i:s');
        $image_id = $this->UUIDV4();
        $account_id = $this->request->getAttribute('tokenInfo')->data;
        $input['image_name'] = empty($input['image_name']) ? null : $input['image_name'];

        $sqlQuery = "INSERT INTO inspection_storage
                        SET
                            project_id = :project_id, 
                            inspection_id = :inspection_id, 
                            image_id = :image_id, 
                            image_name = :image_name, 
                            image_path = :image_path, 
                            image_size = :image_size, 
                            uploaded_at = :uploaded_at, 
                            uploaded_by = :uploaded_by";

        $stmt = $pdo->prepare($sqlQuery);
        $stmt->bindValue(':project_id', $input['project_id']);
        $stmt->bindValue(':inspection_id', $input['inspection_id']);
        $stmt->bindValue(':image_id', $image_id);
        $stmt->bindValue(':image_name', $input['image_name']);
        $stmt->bindValue(':image_path', $runUploadImage['file_path']);
        $stmt->bindValue(':image_size', (int)$runUploadImage['file_size']);
        $stmt->bindValue(':uploaded_at', $date_now);
        $stmt->bindValue(':uploaded_by', $account_id);
        $createProjectFile = $stmt->execute();

        if (!$createProjectFile) return $this->respondWithData('Create database fail', 404);

        return $this->respondWithData(['image_id' => $image_id]);
    }
}
