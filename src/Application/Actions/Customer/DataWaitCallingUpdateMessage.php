<?php

declare(strict_types=1);

namespace App\Application\Actions\Customer;

use Psr\Http\Message\ResponseInterface as Response;

class DataWaitCallingUpdateMessage extends MainAction
{
    protected function action(): Response
    {
        $input = $this->getFormData();
        $allKeys = ["data_id", "employee_message"];
        $requiredKeys = ["data_id", "employee_message"];

        if (!$this->validateInputBody($input, $allKeys, $requiredKeys)) {
            return $this->respondWithData("Bad Request", 400);
        }

        date_default_timezone_set("Asia/Bangkok");
        $DATETIME_NOW = date('Y-m-d H:i:s');
        $updateBy = $this->request->getAttribute('tokenInfo')->data;

        $PDO = $this->pdoConnect($_ENV['DB_PORTAL']);

        $sqlQuery = "UPDATE data_wait_calling
                    SET 
                        employee_message = :employee_message,
                        updated_at = :updated_at,
                        updated_by = :updated_by 
                    WHERE 
                        data_id = :data_id";

        $stmt = $PDO->prepare($sqlQuery);
        $stmt->bindValue(':data_id', $input['data_id']);
        $stmt->bindValue(':employee_message', $input['employee_message']);
        $stmt->bindValue(':updated_at', $DATETIME_NOW);
        $stmt->bindValue(':updated_by', $updateBy);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            return $this->respondWithData("Updated", 200);
        } else {
            return $this->respondWithData("Failed", 400);
        }
    }
}
