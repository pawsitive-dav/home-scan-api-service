<?php

declare(strict_types=1);

namespace App\Application\Actions\ImageAvatar;

use App\Application\Actions\ImageAvatar\MainAction;
use Psr\Http\Message\ResponseInterface as Response;

class Upload extends MainAction
{
    protected function action(): Response
    {
        $input = $this->getFormData();
        $allKeys = ["image"];
        $requiredKeys = ["image"];

        if (!$this->validateInputBody($input, $allKeys, $requiredKeys)) {
            return $this->respondWithData("Bad Request", 400);
        }

        return $this->respondWithData($input['image']);
    }
}
