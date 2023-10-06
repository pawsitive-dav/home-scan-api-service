<?php

declare(strict_types=1);

namespace App\Application\Actions\Member;

use App\Application\Actions\Action;
use Psr\Log\LoggerInterface;

abstract class MainAction extends Action
{
    public function __construct(LoggerInterface $logger)
    {
        parent::__construct($logger);
    }

    protected function hashPassword($password)
    {
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        return $hashed_password;
    }
}
