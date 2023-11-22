<?php

declare(strict_types=1);

namespace App\Application\Actions\Project;

use Psr\Http\Message\ResponseInterface as Response;

class ProjectGetCheckerTeam extends MainAction
{
    protected function action(): Response
    {
        $project_id = $this->resolveArg('id');

        if (!$project_id) return $this->respondWithData('Not found', 404);

        $pdo = $this->pdoConnect($_ENV['DB_PORTAL']);

        $sqlQuery = "SELECT
                        mi.account_id,
                        mi.avatar_path,
                        mi.first_name,
                        mi.last_name,
                        mi.code_name,
                        mi.member_role
                    FROM project_teams_checker ptc
                    INNER JOIN member_info mi ON ptc.checker_team = mi.account_id
                    WHERE project_id = :project_id";

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
