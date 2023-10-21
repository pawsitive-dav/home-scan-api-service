<?php

declare(strict_types=1);

namespace App\Application\Actions\Customer;

use Psr\Http\Message\ResponseInterface as Response;

class DataBacklogVerify extends MainAction
{
    protected function action(): Response
    {
        $input = $this->getFormData();
        $allKeys = ["type", "full_name", "mobile_number"];
        $requiredKeys = ["type"];

        if (!$this->validateInputBody($input, $allKeys, $requiredKeys)) {
            return $this->respondWithData("Bad Request", 400);
        }
        $verrifyResult = null;

        if ($input['type'] === "name") {
            $verrifyResult = $this->VerifyNameData($input['full_name']);
        }

        if ($input['type'] === "mobile") {
            $verrifyResult = $this->VerifyMobileNumberData($input['mobile_number']);
        }

        return $this->respondWithData($verrifyResult);
    }
}
