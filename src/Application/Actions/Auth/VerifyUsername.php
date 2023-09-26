<?php

declare(strict_types=1);

namespace App\Application\Actions\Auth;

use Psr\Http\Message\ResponseInterface as Response;

class VerifyUsername extends AuthAction
{
    protected function action(): Response
    {
        $input = $this->getFormData();
        $all_keys = ["username"];
        $required_keys = ["username"];

        if ($this->validateInputBody($input, $all_keys, $required_keys)) {
            $verifyResult = $this->verifyUsername($input['username']);
            if ($verifyResult) {
                return $this->respondWithData("Username is already taken!", 409);
            } else {
                return $this->respondWithData("Username is available");
            }
        } else {
            return $this->respondWithData("Bad Request", 400);
        }
    }
}
