<?php

declare(strict_types=1);

namespace App\Application\Actions\Auth;

use Psr\Http\Message\ResponseInterface as Response;

class LoginExternal extends AuthAction
{
    protected function action(): Response
    {
        $input = $this->getFormData();
        $allKeys = ["external_id"];
        $requiredKeys = ["external_id"];

        if (!$this->validateInputBody($input, $allKeys, $requiredKeys)) {
            return $this->respondWithData("Bad Request", 400);
        }

        $externalIdVerifyResult = $this->verifyExternalId($input['external_id']);

        if (!$externalIdVerifyResult) {
            return $this->respondWithData("You are not a member yet.", 401);
        }

        if ($externalIdVerifyResult["approval"] === 0) {
            return $this->respondWithData("Your account is not approved yet!", 401);
        }

        if ($externalIdVerifyResult["account_status"] === "suspended") {
            return $this->respondWithData("Your account is suspended.", 401);
        }

        date_default_timezone_set("Asia/Bangkok");
        $dateNow = date('Y-m-d H:i:s');

        $pdo = $this->pdoConnect($_ENV['DB_MEMBER']);
        $dbTable = 'account';
        $sqlQuery = "UPDATE " . $dbTable . "
                        SET
                            last_login = :last_login 
                        WHERE 
                            account_id = :account_id";

        $stmt = $pdo->prepare($sqlQuery);
        $stmt->bindValue(':last_login', $dateNow);
        $stmt->bindValue(':account_id', $externalIdVerifyResult["account_id"]);
        $stmt->execute();

        $refreshToken = $this->createRefreshToken($externalIdVerifyResult["account_id"]);

        return $this->respondWithData([
            "refreshToken" => $refreshToken
        ]);
    }
}
