<?php

declare(strict_types=1);

namespace App\Application\Actions\Auth;

use Psr\Http\Message\ResponseInterface as Response;

class RegisterExternal extends AuthAction
{
    protected function action(): Response
    {
        $input = $this->getFormData();
        $allKeys = ["register_by", "external_id", "first_name", "last_name", "code_name"];
        $requiredKeys = ["register_by", "external_id", "first_name", "last_name", "code_name"];

        if (!$this->validateInputBody($input, $allKeys, $requiredKeys)) {
            return $this->respondWithData("Bad Request", 400);
        }

        date_default_timezone_set("Asia/Bangkok");
        $dateNow = date('Y-m-d H:i:s');
        $UUID = $this->UUIDV4();

        $pdo = $this->pdoConnect($_ENV['DB_MEMBER']);

        $insertAccountSuccess = $this->insertAccount($pdo, $UUID, $input['register_by'], $input['external_id'], $dateNow);

        if (!$insertAccountSuccess) {
            return $this->respondWithData("Failed to insert account", 400);
        }

        $insertMemberInfoSuccess = $this->insertMemberInfo($pdo, $UUID, $input['first_name'], $input['last_name'], $input['code_name']);

        if (!$insertMemberInfoSuccess) {
            return $this->respondWithData("Failed to insert member info", 400);
        }

        return $this->respondWithData("Create Account Successfully.", 201);
    }

    private function insertAccount($pdo, $UUID, $registerBy, $externalId, $dateNow)
    {
        // Insert into account table
        $dbTableAccount = 'account';
        $sqlQueryAccount = "INSERT INTO " . $dbTableAccount . "
                            SET
                                account_id = :account_id, 
                                external_id = :external_id, 
                                account_status = :account_status, 
                                created_by = :created_by, 
                                created_at = :created_at";

        $stmtAccount = $pdo->prepare($sqlQueryAccount);
        $stmtAccount->bindValue(':account_id', $UUID);
        $stmtAccount->bindValue(':external_id', $externalId);
        $stmtAccount->bindValue(':account_status', 'waiting');
        $stmtAccount->bindValue(':created_by', $registerBy);
        $stmtAccount->bindValue(':created_at', $dateNow);

        return $stmtAccount->execute();
    }

    private function insertMemberInfo($pdo, $UUID, $firstName, $lastName, $codeName)
    {
        // Insert into member_info table
        $dbTableMemberInfo = 'member_info';
        $sqlQueryMemberInfo = "INSERT INTO " . $dbTableMemberInfo . "
                            SET
                                account_id = :account_id, 
                                first_name = :first_name, 
                                last_name = :last_name, 
                                code_name = :code_name";

        $stmtMemberInfo = $pdo->prepare($sqlQueryMemberInfo);
        $stmtMemberInfo->bindValue(':account_id', $UUID);
        $stmtMemberInfo->bindValue(':first_name', $firstName);
        $stmtMemberInfo->bindValue(':last_name', $lastName);
        $stmtMemberInfo->bindValue(':code_name', $codeName);

        return $stmtMemberInfo->execute();
    }
}
