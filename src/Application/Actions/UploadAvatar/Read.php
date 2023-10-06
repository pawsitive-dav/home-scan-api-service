<?php

declare(strict_types=1);

namespace App\Application\Actions\UploadAvatar;

use Psr\Http\Message\ResponseInterface as Response;

class Read extends MainAction
{
    protected function action(): Response
    {
        $imageId = $this->resolveArg('id');
        $imagePath = '../resources/avatar/' . $imageId . '.jpeg';

        if (file_exists($imagePath)) {
            $imageData = file_get_contents($imagePath);
            $imageDataEncoded = base64_encode($imageData);
            $imageSrc = 'data:image/jpeg;base64,' . $imageDataEncoded;
            return $this->respondWithData($imageSrc);
        } else {
            return $this->respondWithData("Fail", 404);
        }
    }
}
