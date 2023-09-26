<?php

declare(strict_types=1);

namespace App\Application\Actions\Auth;

use App\Application\Actions\Action;
use Psr\Log\LoggerInterface;
use Dotenv\Dotenv;
use Firebase\JWT\JWT;
use PDO;

abstract class AuthAction extends Action
{
    public function __construct(LoggerInterface $logger)
    {
        parent::__construct($logger);
        $dotenv = Dotenv::createImmutable(__DIR__ . '../../../../../');
        $dotenv->load();
    }

    protected function createRefreshToken($data)
    {
        date_default_timezone_set("Asia/Bangkok");
        $payload = [
            'iss' => $_ENV['APP_NAME'],
            'iat' => time(),
            'exp' => time() + 172800, // 48 hour
            // 'exp' => time() + 14400, // 4 hour
            'data' => $data ? $data : ''
        ];
        return JWT::encode($payload, $_ENV['REFRESH_SECRET_KEY'], 'HS256');
    }

    protected function createAccessToken($data)
    {
        date_default_timezone_set("Asia/Bangkok");
        $payload = [
            'iss' => $_ENV['APP_NAME'],
            'iat' => time(),
            'exp' => time() + 120, // 2 minutes
            'data' => $data ? $data : ''
        ];
        return JWT::encode($payload, $_ENV['ACCESS_SECRET_KEY'], 'HS256');
    }

    protected function hashPassword($password)
    {
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        return $hashed_password;
    }

    protected function verifyPassword($password, $hashedPassword)
    {
        return password_verify($password, $hashedPassword);
    }

    protected function verifyUsername($username)
    {
        $pdo = $this->pdoConnect($_ENV['DB_MEMBER']);
        $dbTable = 'account';
        $sqlQuery = "SELECT * FROM " . $dbTable . " WHERE username = :username";
        $stmt = $pdo->prepare($sqlQuery);
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            return $stmt->fetchAll()[0];
        } else {
            return false;
        }
    }

    protected function verifyExternalId($external_id)
    {
        $pdo = $this->pdoConnect($_ENV['DB_MEMBER']);
        $dbTable = 'account';
        $sqlQuery = "SELECT * FROM " . $dbTable . " WHERE external_id = :external_id";
        $stmt = $pdo->prepare($sqlQuery);
        $stmt->bindParam(':external_id', $external_id);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            return $stmt->fetchAll()[0];
        } else {
            return false;
        }
    }
}
