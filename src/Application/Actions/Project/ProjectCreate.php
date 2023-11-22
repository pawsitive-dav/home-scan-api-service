<?php

declare(strict_types=1);

namespace App\Application\Actions\Project;

use Psr\Http\Message\ResponseInterface as Response;

class ProjectCreate extends MainAction
{
    protected function action(): Response
    {
        $input = $this->getFormData();
        $allKeys = ["project_data",];
        $requiredKeys = ["project_data"];

        if (!$this->validateInputBody($input, $allKeys, $requiredKeys)) {
            return $this->respondWithData("Bad Request", 400);
        }

        $inputData = $input['project_data'];

        date_default_timezone_set("Asia/Bangkok");
        $DATE_NOW = date('Y-m-d H:i:s');
        $projectId = $this->UUIDV4();
        $PDO = $this->pdoConnect($_ENV['DB_PORTAL']);
        $account_id = $this->request->getAttribute('tokenInfo')->data;

        $projectDetail = $inputData['project_detail'];
        $runCreateProjectDetail = $this->createProjectDetail($PDO, $projectId, $DATE_NOW, $account_id, $projectDetail);
        if (!$runCreateProjectDetail) return $this->respondWithData("Create project detail fail", 404);

        $typeDetail = $inputData['type_detail'];
        $runCreateTypeDetail = $this->createTypeDetail($PDO, $projectId, $typeDetail);
        if (!$runCreateTypeDetail) return $this->respondWithData("Create type detail fail", 404);

        $customerDetail = $inputData['customer_detail'];
        $runCreateCustomerDetail = $this->createCustomerDetail($PDO, $projectId, $customerDetail);
        if (!$runCreateCustomerDetail) return $this->respondWithData("Create customer detail fail", 404);

        $projectCoordinator = $inputData['project_coordinator'];
        $runCreateProjectCoordinator = $this->createProjectCoordinator($PDO, $projectId, $projectCoordinator);
        if (!$runCreateProjectCoordinator) return $this->respondWithData("Create project coordinator fail", 404);

        $projectTeams = $inputData['project_teams'];
        $this->createProjectTeams($PDO, $projectId, $projectTeams);

        $fileImage = $inputData['file_image'];
        $runUploadFile = $this->uploadProjectFile($PDO, $projectId, $fileImage);
        if (!$runUploadFile) return $this->respondWithData("Upload project file fail", 404);

        return $this->respondWithData('Create project successfully', 201);
    }

    private function createProjectDetail($PDO, $projectId, $DATE_NOW, $account_id, $data)
    {
        $sqlQuery = "INSERT INTO project_detail
                            SET
                                project_id = :project_id, 
                                project_status = :project_status, 
                                project_name = :project_name,
                                project_note = :project_note,
                                created_at = :created_at, 
                                created_by = :created_by";

        $stmt = $PDO->prepare($sqlQuery);
        $stmt->bindValue(':project_id', $projectId);
        $stmt->bindValue(':project_status', 'to-do');
        $stmt->bindValue(':project_name', $data['project_name']);
        $stmt->bindValue(':project_note', $data['project_note']);
        $stmt->bindValue(':created_at', $DATE_NOW);
        $stmt->bindValue(':created_by', $account_id);

        return $stmt->execute();
    }

    private function createTypeDetail($PDO, $projectId, $data)
    {
        $sqlQuery = "INSERT INTO project_type_detail
                        SET
                            project_id = :project_id, 
                            project_type = :project_type,
                            type_address = :type_address,
                            type_usable_area = :type_usable_area";

        $stmt = $PDO->prepare($sqlQuery);
        $stmt->bindValue(':project_id', $projectId);
        $stmt->bindValue(':project_type', $data['project_type']);
        $stmt->bindValue(':type_address', $data['type_address']);
        $stmt->bindValue(':type_usable_area', $data['type_usable_area']);

        return $stmt->execute();
    }

    private function createCustomerDetail($PDO, $projectId, $data)
    {
        $sqlQuery = "INSERT INTO project_customer
                        SET
                            project_id = :project_id, 
                            customer_name = :customer_name,
                            customer_phone = :customer_phone,
                            customer_email = :customer_email";

        $stmt = $PDO->prepare($sqlQuery);
        $stmt->bindValue(':project_id', $projectId);
        $stmt->bindValue(':customer_name', $data['customer_name']);
        $stmt->bindValue(':customer_phone', $data['customer_phone']);
        $stmt->bindValue(':customer_email', $data['customer_email']);

        return $stmt->execute();
    }

    private function createProjectCoordinator($PDO, $projectId, $data)
    {
        $sqlQuery = "INSERT INTO project_coordinator
                        SET
                            project_id = :project_id, 
                            coordinator_name = :coordinator_name,
                            coordinator_phone = :coordinator_phone,
                            coordinator_email = :coordinator_email";

        $stmt = $PDO->prepare($sqlQuery);
        $stmt->bindValue(':project_id', $projectId);
        $stmt->bindValue(':coordinator_name', $data['coordinator_name']);
        $stmt->bindValue(':coordinator_phone', $data['coordinator_phone']);
        $stmt->bindValue(':coordinator_email', $data['coordinator_email']);

        return $stmt->execute();
    }

    private function createProjectTeams($PDO, $projectId, $data)
    {
        $sqlProjectOwner = "INSERT INTO project_teams
                        SET
                            project_id = :project_id, 
                            project_owner = :project_owner,
                            checker_supervisor = :checker_supervisor";

        $stmtProjectOwner = $PDO->prepare($sqlProjectOwner);
        $stmtProjectOwner->bindValue(':project_id', $projectId);
        $stmtProjectOwner->bindValue(':project_owner', $data['project_owner']);
        $stmtProjectOwner->bindValue(':checker_supervisor', $data['checker_supervisor']);
        $stmtProjectOwner->execute();

        if ($data['checker_team']) {
            foreach ($data['checker_team'] as $checkerId) {
                $sqlCheckerTeam = "INSERT INTO project_teams_checker
                        SET
                            project_id = :project_id, 
                            checker_team = :checker_team";

                $stmtCheckerTeam = $PDO->prepare($sqlCheckerTeam);
                $stmtCheckerTeam->bindValue(':project_id', $projectId);
                $stmtCheckerTeam->bindValue(':checker_team', $checkerId);
                $stmtCheckerTeam->execute();
            }
        }
    }

    private function uploadProjectFile($PDO, $projectId, $data)
    {
        if ($data['project_image']) {
            $uploadProjectImage = $this->uploadImage($data['project_image']);
            if (!$uploadProjectImage) return $this->respondWithData('Upload main image fail', 404);
            $this->insertProjectFile($PDO, $projectId, $uploadProjectImage, 'main');
        }

        if ($data['type_plan1']) {
            $uploadTypePlan1 = $this->uploadImage($data['type_plan1']);
            if (!$uploadTypePlan1) return $this->respondWithData('Upload type plan 1 fail', 404);
            $this->insertProjectFile($PDO, $projectId, $uploadTypePlan1, 'plan1');
        }

        if ($data['type_plan2']) {
            $uploadTypePlan2 = $this->uploadImage($data['type_plan2']);
            if (!$uploadTypePlan2) return $this->respondWithData('Upload type plan 2 fail', 404);
            $this->insertProjectFile($PDO, $projectId, $uploadTypePlan2, 'plan2');
        }

        if ($data['type_plan3']) {
            $uploadTypePlan3 = $this->uploadImage($data['type_plan3']);
            if (!$uploadTypePlan3) return $this->respondWithData('Upload type plan 3 fail', 404);
            $this->insertProjectFile($PDO, $projectId, $uploadTypePlan3, 'plan3');
        }

        if ($data['type_plan4']) {
            $uploadTypePlan4 = $this->uploadImage($data['type_plan4']);
            if (!$uploadTypePlan4) return $this->respondWithData('Upload type plan 4 fail', 404);
            $this->insertProjectFile($PDO, $projectId, $uploadTypePlan4, 'plan4');
        }

        return true;
    }

    private function insertProjectFile($PDO, $projectId, $uploadImage, $fileType)
    {
        $sqlProjectFile = "INSERT INTO project_file
                        SET
                            project_id = :project_id, 
                            file_type = :file_type, 
                            image_path = :image_path, 
                            image_size = :image_size";

        $stmtProjectFile = $PDO->prepare($sqlProjectFile);
        $stmtProjectFile->bindValue(':project_id', $projectId);
        $stmtProjectFile->bindValue(':file_type', $fileType);
        $stmtProjectFile->bindValue(':image_path', $uploadImage['file_path']);
        $stmtProjectFile->bindValue(':image_size', (int)$uploadImage['file_size']);
        $createProjectFile = $stmtProjectFile->execute();
        if (!$createProjectFile) return $this->respondWithData('Create project file fail', 404);
    }
}
