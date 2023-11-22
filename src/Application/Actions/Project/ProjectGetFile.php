<?php

declare(strict_types=1);

namespace App\Application\Actions\Project;

use Psr\Http\Message\ResponseInterface as Response;

class ProjectGetFile extends MainAction
{
    protected function action(): Response
    {
        $project_id = $this->resolveArg('id');

        if (!$project_id) return $this->respondWithData('Not found', 404);

        $pdo = $this->pdoConnect($_ENV['DB_PORTAL']);

        $sqlQuery = "SELECT * FROM project_file WHERE project_id = :project_id";

        $stmt = $pdo->prepare($sqlQuery);
        $stmt->bindValue(":project_id", $project_id);
        $stmt->execute();

        $allData = $stmt->fetchAll();

        if (empty($allData)) {
            return $this->respondWithData(null, 200);
        }

        return $this->respondWithData($allData);
    }
}
