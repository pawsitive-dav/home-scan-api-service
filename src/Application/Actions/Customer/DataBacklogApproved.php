<?php

declare(strict_types=1);

namespace App\Application\Actions\Customer;

use Psr\Http\Message\ResponseInterface as Response;

class DataBacklogApproved extends MainAction
{
    protected function action(): Response
    {
        $input = $this->getFormData();
        $allKeys = ["data_id"];
        $requiredKeys = ["data_id"];

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
                        (data_id, data_status, full_name, mobile_number, branch_selected, customer_message, register_from, approved_at)
                        VALUES
                        (:data_id, :data_status, :full_name, :mobile_number, :branch_selected, :customer_message, :register_from, :approved_at)";

        $stmt = $PDO->prepare($sqlQueryUpdate);
        $stmt->bindValue(':data_id', $resultData['data_id']);
        $stmt->bindValue(':data_status', "new");
        $stmt->bindValue(':full_name', $resultData['full_name']);
        $stmt->bindValue(':mobile_number', $resultData['mobile_number']);
        $stmt->bindValue(':branch_selected', $resultData['branch_selected']);
        $stmt->bindValue(':customer_message', $resultData['customer_message']);
        $stmt->bindValue(':register_from', $resultData['register_from']);
        $stmt->bindValue(':approved_at', $DATETIME_NOW);
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
