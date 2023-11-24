<?php

declare(strict_types=1);

namespace App\Application\Actions\Project;

use Psr\Http\Message\ResponseInterface as Response;

class InspectionReportSavePDF extends MainAction
{
    protected function action(): Response
    {
        $input = $this->getFormData();
        $allKeys = ["project_id", "inspection_id", "report_id", "pdf_base64"];
        $requiredKeys = ["project_id", "inspection_id", "report_id", "pdf_base64"];

        if (!$this->validateInputBody($input, $allKeys, $requiredKeys)) {
            return $this->respondWithData("Bad Request", 400);
        }

        return $this->respondWithData($input);
    }
}
