<?php

declare(strict_types=1);

namespace App\Application\Actions\Role;

use App\Application\Actions\Action;
use Psr\Log\LoggerInterface;
use Dotenv\Dotenv;

abstract class MainAction extends Action
{
    public function __construct(LoggerInterface $logger)
    {
        parent::__construct($logger);
        $dotenv = Dotenv::createImmutable(__DIR__ . '../../../../../');
        $dotenv->load();
    }
}
