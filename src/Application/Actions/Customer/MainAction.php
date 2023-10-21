<?php

declare(strict_types=1);

namespace App\Application\Actions\Customer;

use App\Application\Actions\Action;
use Psr\Log\LoggerInterface;

abstract class MainAction extends Action
{
    public function __construct(LoggerInterface $logger)
    {
        parent::__construct($logger);
    }

    protected function VerifyNameData($fullName)
    {
        if (empty($fullName)) return false;

        $PDO = $this->pdoConnect($_ENV['DB_PORTAL']);

        $tables = ["data_customer", "data_backlog", "data_wait_calling", "data_external", "data_internal", "data_booking", "data_recheck"];
        $foundTables = [];

        foreach ($tables as $table) {
            $sqlQuery = "SELECT COUNT(*) FROM $table WHERE full_name = :full_name";
            $stmt = $PDO->prepare($sqlQuery);
            $stmt->bindValue(':full_name', $fullName);
            $stmt->execute();

            if ($stmt->fetchColumn() > 0) {
                $foundTables[] = $table;
                return $foundTables;
            }
        }
        return false;
    }

    protected function VerifyMobileNumberData($mobileNumber)
    {
        if (empty($mobileNumber)) return false;

        $PDO = $this->pdoConnect($_ENV['DB_PORTAL']);

        $tables = ["data_customer", "data_backlog", "data_wait_calling", "data_external", "data_internal", "data_booking", "data_recheck"];
        $foundTables = [];

        foreach ($tables as $table) {
            $sqlQuery = "SELECT COUNT(*) FROM $table WHERE mobile_number = :mobile_number";
            $stmt = $PDO->prepare($sqlQuery);
            $stmt->bindValue(':mobile_number', $mobileNumber);
            $stmt->execute();

            if ($stmt->fetchColumn() > 0) {
                $foundTables[] = $table;
                return $foundTables;
            }
        }
        return false;
    }
}
