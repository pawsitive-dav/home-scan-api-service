<?php

declare(strict_types=1);

namespace App\Application\Actions\Customer;

use Psr\Http\Message\ResponseInterface as Response;

class DataBacklogApproved extends MainAction
{
    protected function action(): Response
    {
        $input = $this->getFormData();
        $allKeys = ["data_id", "assignee"];
        $requiredKeys = ["data_id", "assignee"];

        if (!$this->validateInputBody($input, $allKeys, $requiredKeys)) {
            return $this->respondWithData("Bad Request", 400);
        }

        $PDO = $this->pdoConnect($_ENV['DB_PORTAL']);

        $sqlQuery = "SELECT data_id, data_status, full_name, mobile_number, 
                        branch_selected, customer_message, register_from, created_at 
                FROM data_backlog WHERE data_id = :data_id";

        $stmt = $PDO->prepare($sqlQuery);
        $stmt->bindValue(':data_id', $input['data_id']);
        $stmt->execute();

        if ($stmt->rowCount() == 0) return $this->respondWithData("Failed Get", 404);

        date_default_timezone_set("Asia/Bangkok");
        $DATETIME_NOW = date('Y-m-d H:i:s');
        $resultData = $stmt->fetchAll()[0];

        $sqlQueryUpdate = "INSERT INTO data_wait_calling
                        SET
                            data_id = :data_id,
                            data_status = :data_status,
                            full_name = :full_name,
                            mobile_number = :mobile_number,
                            branch_selected = :branch_selected,
                            customer_message = :customer_message,
                            register_from = :register_from,
                            approved_at = :approved_at,
                            assignee = :assignee";

        $stmt = $PDO->prepare($sqlQueryUpdate);
        $stmt->bindValue(':data_id', $resultData['data_id']);
        $stmt->bindValue(':data_status', "ready-for-call");
        $stmt->bindValue(':full_name', $resultData['full_name']);
        $stmt->bindValue(':mobile_number', $resultData['mobile_number']);
        $stmt->bindValue(':branch_selected', $resultData['branch_selected']);
        $stmt->bindValue(':customer_message', $resultData['customer_message']);
        $stmt->bindValue(':register_from', $resultData['register_from']);
        $stmt->bindValue(':approved_at', $DATETIME_NOW);
        $stmt->bindValue(':assignee', $input['assignee']);
        $stmt->execute();

        if ($stmt->rowCount() == 0) return $this->respondWithData("Failed Update", 404);

        $sqlQueryDelete = "DELETE FROM data_backlog WHERE data_id = :data_id";
        $stmt = $PDO->prepare($sqlQueryDelete);
        $stmt->bindValue(':data_id', $resultData['data_id']);
        $stmt->execute();

        if ($stmt->rowCount() == 0) return $this->respondWithData("Failed Delete", 404);

        return $this->respondWithData("Data Approved", 200);
    }
}
