<?php

declare(strict_types=1);

namespace App\Application\Actions\Website;

use Psr\Http\Message\ResponseInterface as Response;

class PromotionGreate extends MainAction
{
    protected function action(): Response
    {
        $input = $this->getFormData();
        $allKeys = ["promotion_name", "promotion_status", "image_alt", "promotion_link", "promotion_note", "banner_lg", "banner_md", "banner_sm"];
        $requiredKeys = ["promotion_name", "promotion_status", "image_alt", "promotion_link", "promotion_note", "banner_lg", "banner_md", "banner_sm"];

        if (!$this->validateInputBody($input, $allKeys, $requiredKeys)) {
            return $this->respondWithData("Bad Request", 400);
        }

        date_default_timezone_set("Asia/Bangkok");
        $DATETIME_NOW = date('Y-m-d H:i:s');
        $UUID = $this->UUIDV4();
        $PDO = $this->pdoConnect($_ENV['DB_PORTAL']);
        $account_id = $this->request->getAttribute('tokenInfo')->data;

        $uploadImgLg = $this->uploadImage($input['banner_lg']);
        $uploadImgMd = $this->uploadImage($input['banner_md']);
        $uploadImgSm = $this->uploadImage($input['banner_sm']);

        if (!$uploadImgLg && !$uploadImgMd && !$uploadImgSm) {
            return $this->respondWithData("Failed to upluad banner", 400);
        }

        $createPromotionData = $this->createPromotion($PDO, $UUID, $DATETIME_NOW, $account_id, $input, $uploadImgLg, $uploadImgMd, $uploadImgSm);
        if (!$createPromotionData) {
            return $this->respondWithData("Failed to create", 400);
        }

        return $this->respondWithData("Create promotion successfully.", 201);
    }

    private function createPromotion($PDO, $UUID, $DATETIME_NOW, $account_id, $input, $img_lg, $img_md, $img_sm)
    {
        $sqlQuery = "INSERT INTO web_promotion
                            SET
                                promotion_id = :promotion_id, 
                                promotion_name = :promotion_name, 
                                promotion_status = :promotion_status, 
                                image_alt = :image_alt, 
                                promotion_link = :promotion_link, 
                                promotion_note = :promotion_note, 
                                img_lg = :img_lg, 
                                img_md = :img_md, 
                                img_sm = :img_sm, 
                                created_at = :created_at, 
                                created_by = :created_by";

        $stmtAccount = $PDO->prepare($sqlQuery);
        $stmtAccount->bindValue(':account_id', $UUID);
        $stmtAccount->bindValue(':promotion_name', $input['promotion_name']);
        $stmtAccount->bindValue(':promotion_status', $input['promotion_status']);
        $stmtAccount->bindValue(':image_alt', $input['image_alt']);
        $stmtAccount->bindValue(':promotion_link', $input['promotion_link']);
        $stmtAccount->bindValue(':promotion_note', $input['promotion_note']);
        $stmtAccount->bindValue(':img_lg', $img_lg);
        $stmtAccount->bindValue(':img_md', $img_md);
        $stmtAccount->bindValue(':img_sm', $img_sm);
        $stmtAccount->bindValue(':created_at', $DATETIME_NOW);
        $stmtAccount->bindValue(':created_by', $account_id);

        return $stmtAccount->execute();
    }
}
