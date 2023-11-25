<?php

declare(strict_types=1);

namespace App\Application\Actions\Project;

use Psr\Http\Message\ResponseInterface as Response;

class InspectionReportGetLocationList extends MainAction
{
    protected function action(): Response
    {
        $input = $this->getFormData();
        $allKeys = ["project_id", "inspection_id"];
        $requiredKeys = ["project_id", "inspection_id"];

        if (!$this->validateInputBody($input, $allKeys, $requiredKeys)) {
            return $this->respondWithData("Bad Request", 400);
        }

        $pdo = $this->pdoConnect($_ENV['DB_PORTAL']);

        $locationQuery = "SELECT inspection_id, location_id, location_name
                          FROM inspection_location
                          WHERE project_id = :project_id AND inspection_id = :inspection_id";

        $locationStmt = $pdo->prepare($locationQuery);
        $locationStmt->bindValue(':project_id', $input['project_id']);
        $locationStmt->bindValue(':inspection_id', $input['inspection_id']);
        $locationStmt->execute();

        $locationDataList = $locationStmt->fetchAll();

        $deflectQuery = "SELECT 
                            id, 
                            location_id,
                            image_id, 
                            deflect_status,
                            deflect_detail,
                            created_at,
                            created_by,
                            updated_at,
                            updated_by,
                            update_status_at,
                            update_status_by
                        FROM inspection_location_deflect
                        WHERE location_id = :location_id";

        $deflectStmt = $pdo->prepare($deflectQuery);

        $memberQuery = "SELECT 
                            account_id, 
                            avatar_path,
                            code_name, 
                            first_name,
                            last_name
                        FROM member_info
                        WHERE account_id = :account_id";

        $memberStmt = $pdo->prepare($memberQuery);

        $combinedDataList = [];

        foreach ($locationDataList as $locationData) {
            $deflectStmt->bindValue(':location_id', $locationData['location_id']);
            $deflectStmt->execute();

            $deflectDataList = $deflectStmt->fetchAll();

            foreach ($deflectDataList as &$deflectData) {
                $memberStmt->bindValue(':account_id', $deflectData['created_by']);
                $memberStmt->execute();
                $createdByData = $memberStmt->fetch();
                $deflectData['created_by'] = $createdByData;

                $memberStmt->bindValue(':account_id', $deflectData['updated_by']);
                $memberStmt->execute();
                $updatedByData = $memberStmt->fetch();
                $deflectData['updated_by'] = $updatedByData;

                $memberStmt->bindValue(':account_id', $deflectData['update_status_by']);
                $memberStmt->execute();
                $updateStatusByData = $memberStmt->fetch();
                $deflectData['update_status_by'] = $updateStatusByData;

                $imageQuery = "SELECT 
                                    image_name,
                                    image_path, 
                                    image_size
                                FROM inspection_storage
                                WHERE image_id = :image_id";

                $imageStmt = $pdo->prepare($imageQuery);
                $imageStmt->bindValue(':image_id', $deflectData['image_id']);
                $imageStmt->execute();

                $imageData = $imageStmt->fetch();

                $deflectData['image_name'] = $imageData['image_name'];
                $deflectData['image_path'] = $imageData['image_path'];
                $deflectData['image_size'] = $imageData['image_size'];
            }

            $combinedDataList[] = [
                'deflect_list' => $deflectDataList,
                'inspection_id' => $locationData['inspection_id'],
                'location_id' => $locationData['location_id'],
                'location_name' => $locationData['location_name'],
            ];
        }

        return $this->respondWithData($combinedDataList);
    }
}
