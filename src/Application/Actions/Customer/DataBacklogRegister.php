<?php

declare(strict_types=1);

namespace App\Application\Actions\Customer;

use Psr\Http\Message\ResponseInterface as Response;

class DataBacklogRegister extends MainAction
{
    protected function action(): Response
    {
        $input = $this->getFormData();
        $allKeys = ["full_name", "mobile_number", "branch_selected", "customer_message", "register_from"];
        $requiredKeys = ["full_name", "mobile_number", "branch_selected"];

        if (!$this->validateInputBody($input, $allKeys, $requiredKeys)) {
            return $this->respondWithData("Bad Request", 400);
        }

        if (empty($input['customer_message'])) {
            $input['customer_message'] = null;
        }

        if (empty($input['register_from'])) {
            $input['register_from'] = "other";
        }

        date_default_timezone_set("Asia/Bangkok");
        $DATETIME_NOW = date('Y-m-d H:i:s');
        $UUID = $this->UUIDV4();
        $PDO = $this->pdoConnect($_ENV['DB_PORTAL']);

        $sqlQuery = "INSERT INTO data_backlog
                    SET
                        data_id = :data_id, 
                        data_status = :data_status, 
                        full_name = :full_name, 
                        mobile_number = :mobile_number, 
                        branch_selected = :branch_selected, 
                        customer_message = :customer_message, 
                        register_from = :register_from, 
                        created_at = :created_at";

        $stmt = $PDO->prepare($sqlQuery);
        $stmt->bindValue(':data_id', $UUID);
        $stmt->bindValue(':data_status', "new");
        $stmt->bindValue(':full_name', $input['full_name']);
        $stmt->bindValue(':mobile_number', $input['mobile_number']);
        $stmt->bindValue(':branch_selected', $input['branch_selected']);
        $stmt->bindValue(':customer_message', $input['customer_message']);
        $stmt->bindValue(':register_from', $input['register_from']);
        $stmt->bindValue(':created_at', $DATETIME_NOW);

        if ($stmt->execute()) {
            $rowCount = $stmt->rowCount();
            if ($rowCount > 0) {
                return $this->respondWithData("Register Successfully.", 201);
            } else {
                return $this->respondWithData("Failed to create", 400);
            }
        } else {
            $errorInfo = $stmt->errorInfo();
            return $this->respondWithData("Database error: " . $errorInfo[2], 500);
        }
    }
}
