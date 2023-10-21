<?php

declare(strict_types=1);

namespace App\Application\Actions\Website;

use Psr\Http\Message\ResponseInterface as Response;

class BranchGetList extends MainAction
{
    protected function action(): Response
    {
        $pdo = $this->pdoConnect($_ENV['DB_PORTAL']);
        $sqlQuery = "SELECT * FROM web_branches";
        $stmt = $pdo->prepare($sqlQuery);
        $stmt->execute();
        if ($stmt->rowCount() == 0) return $this->respondWithData("Fail", 404);
        return $this->respondWithData($stmt->fetchAll());
    }
}
