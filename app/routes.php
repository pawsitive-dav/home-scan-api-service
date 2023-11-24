<?php

declare(strict_types=1);

use Slim\App;
use App\Application\Middleware\AccessMiddleware as VerrifyAccessToken;
use App\Application\Middleware\RefreshMiddleware as VerifyRefreshToken;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

use App\Application\Actions\Auth\RegisterPortal;
use App\Application\Actions\Auth\RegisterExternal;

use App\Application\Actions\Auth\AccountApproval;
use App\Application\Actions\Auth\AccountReject;
use App\Application\Actions\Auth\AccountGetApproval;
use App\Application\Actions\Auth\AccountDelete;
use App\Application\Actions\Auth\AccountSetActive;
use App\Application\Actions\Auth\AccountSetSuspend;
use App\Application\Actions\Auth\AccountSetDelete;

use App\Application\Actions\Auth\LoginPortal;
use App\Application\Actions\Auth\LoginExternal;
use App\Application\Actions\Auth\VerifyToken;
use App\Application\Actions\Auth\VerifyUsername;
use App\Application\Actions\Auth\PasswordRequiredReset;
use App\Application\Actions\Auth\PasswordVerifyCode;
use App\Application\Actions\Auth\PasswordReset;

use App\Application\Actions\UploadAvatar\Upload as UploadAvatar;

use App\Application\Actions\Member\GetMemberInfo;
use App\Application\Actions\Member\GetMemberInfoBy;
use App\Application\Actions\Member\GetMemberByRole;
use App\Application\Actions\Member\GetMemberByTeam;
use App\Application\Actions\Member\GetMyInformation;
use App\Application\Actions\Member\UpdatePassword;
use App\Application\Actions\Role\GetRole;

use App\Application\Actions\Setting\TypeGetList;
use App\Application\Actions\Setting\TypeCreate;
use App\Application\Actions\Setting\TypeUpdate;
use App\Application\Actions\Setting\TypeDelete;
use App\Application\Actions\Setting\LocationGetList;
use App\Application\Actions\Setting\LocationCreate;
use App\Application\Actions\Setting\LocationUpdate;
use App\Application\Actions\Setting\LocationDelete;
use App\Application\Actions\Setting\DeflectGetList;
use App\Application\Actions\Setting\DeflectCreate;
use App\Application\Actions\Setting\DeflectUpdate;
use App\Application\Actions\Setting\DeflectDelete;
use App\Application\Actions\Setting\SystemGetList;
use App\Application\Actions\Setting\SystemCreate;
use App\Application\Actions\Setting\SystemUpdate;
use App\Application\Actions\Setting\SystemDelete;

use App\Application\Actions\Project\ProjectGetList;
use App\Application\Actions\Project\ProjectGetCheckerTeam;
use App\Application\Actions\Project\ProjectGetCheckerList;
use App\Application\Actions\Project\ProjectGetDetail;
use App\Application\Actions\Project\ProjectGetFile;
use App\Application\Actions\Project\ProjectCreate;
use App\Application\Actions\Project\ProjectDelete;
use App\Application\Actions\Project\AddProjectTeams;
use App\Application\Actions\Project\DeleteTeamChecker;

use App\Application\Actions\Project\EditProjectName;
use App\Application\Actions\Project\EditProjectNote;
use App\Application\Actions\Project\EditTypeAddress;
use App\Application\Actions\Project\EditTypeUsableArea;
use App\Application\Actions\Project\EditCustomerName;
use App\Application\Actions\Project\EditCustomerPhone;
use App\Application\Actions\Project\EditCustomerEmail;
use App\Application\Actions\Project\EditCoordinatorName;
use App\Application\Actions\Project\EditCoordinatorPhone;
use App\Application\Actions\Project\EditCoordinatorEmail;

use App\Application\Actions\Project\FileDeleteImage;
use App\Application\Actions\Project\FileUploadImage;

use App\Application\Actions\Project\InspcetionCreateDetail;
use App\Application\Actions\Project\InspcetionGetList;
use App\Application\Actions\Project\InspcetionGetDetail;
use App\Application\Actions\Project\InspcetionDelete;
use App\Application\Actions\Project\InspcetionDeleteBeforeStorage;
use App\Application\Actions\Project\InspcetionDeleteStorage;
use App\Application\Actions\Project\InspcetionDeleteStorageGroup;

use App\Application\Actions\Project\InspcetionCreateLocation;
use App\Application\Actions\Project\InspcetionGetLocationList;
use App\Application\Actions\Project\InspcetionGetLocationDetail;
use App\Application\Actions\Project\InspcetionDeleteLocation;
use App\Application\Actions\Project\InspcetionEditLocation;

use App\Application\Actions\Project\InspcetionCreateSystem;
use App\Application\Actions\Project\InspcetionGetSystemList;
use App\Application\Actions\Project\InspcetionGetSystemDetail;
use App\Application\Actions\Project\InspcetionDeleteSystem;
use App\Application\Actions\Project\InspcetionEditSystem;

use App\Application\Actions\Project\InspcetionGetImageList;
use App\Application\Actions\Project\InspcetionUploadImage;
use App\Application\Actions\Project\InspcetionDeleteImage;
use App\Application\Actions\Project\InspcetionEditImageName;

use App\Application\Actions\Project\InspcetionEditLocationDeflectDetail;
use App\Application\Actions\Project\InspcetionGetLocationDeflectList;
use App\Application\Actions\Project\InspcetionCreateLocationDeflect;
use App\Application\Actions\Project\InspcetionGetLocationDeflectDelete;
use App\Application\Actions\Project\InspcetionEditLocationDeflectStatus;
use App\Application\Actions\Project\InspcetionGetLocationDeflectStorage;

use App\Application\Actions\Project\InspcetionEditSystemDeflectDetail;
use App\Application\Actions\Project\InspcetionGetSystemDeflectList;
use App\Application\Actions\Project\InspcetionCreateSystemDeflect;
use App\Application\Actions\Project\InspcetionGetSystemDeflectDelete;
use App\Application\Actions\Project\InspcetionEditSystemDeflectStatus;
use App\Application\Actions\Project\InspcetionGetSystemDeflectStorage;

use App\Application\Actions\Project\InspectionCreateReport;
use App\Application\Actions\Project\InspectionGetReport;
use App\Application\Actions\Project\InspectionDeleteReport;
use App\Application\Actions\Project\InspectionGetReportDetail;
use App\Application\Actions\Project\InspectionReportAddNote;
use App\Application\Actions\Project\InspectionReportGetNoteList;
use App\Application\Actions\Project\InspectionReportDeleteNote;
use App\Application\Actions\Project\InspectionReportEditNote;
use App\Application\Actions\Project\InspectionReportStatusApproval;
use App\Application\Actions\Project\InspectionReportStatusApprovalCancel;
use App\Application\Actions\Project\InspectionReportStatusApproved;
use App\Application\Actions\Project\InspectionReportGetImageBase64;
use App\Application\Actions\Project\InspectionReportSavePDF;

use App\Application\Actions\Project\InspectionReportGetLocationList;

use App\Application\Actions\Project\InspectionReportGetSystemList;


return function (App $app) {
    $app->options('/{routes:.*}', function (Request $request, Response $response) {
        return $response;
    });

    $app->group('/v1', function (Group $group) {
        $group->group('/auth', function (Group $group) {
            $group->group('/register', function (Group $group) {
                $group->post('/portal', RegisterPortal::class);
                $group->post('/external', RegisterExternal::class);
            });
            $group->group('/account', function (Group $group) {
                $group->get('/approval', AccountGetApproval::class)->add(VerrifyAccessToken::class);
                $group->post('/approval', AccountApproval::class)->add(VerrifyAccessToken::class);
                $group->post('/reject', AccountReject::class)->add(VerrifyAccessToken::class);
                $group->post('/delete', AccountDelete::class)->add(VerrifyAccessToken::class);
                $group->post('/set-active', AccountSetActive::class)->add(VerrifyAccessToken::class);
                $group->post('/set-suspend', AccountSetSuspend::class)->add(VerrifyAccessToken::class);
                $group->post('/set-delete', AccountSetDelete::class)->add(VerrifyAccessToken::class);
            });
            $group->group('/login', function (Group $group) {
                $group->post('/portal', LoginPortal::class);
                $group->post('/external', LoginExternal::class);
            });
            $group->group('/verify', function (Group $group) {
                $group->post('/token', VerifyToken::class)->add(VerifyRefreshToken::class);
                $group->post('/username', VerifyUsername::class);
            });
            $group->group('/password', function (Group $group) {
                $group->post('/required-reset', PasswordRequiredReset::class);
                $group->post('/verify-code', PasswordVerifyCode::class);
                $group->post('/reset', PasswordReset::class);
            });
        });

        $group->group('/member', function (Group $group) {
            $group->get('/', GetMemberInfo::class)->add(VerrifyAccessToken::class);
            $group->get('/get-by/{id}', GetMemberInfoBy::class)->add(VerrifyAccessToken::class);
            $group->get('/by-role/{id}', GetMemberByRole::class)->add(VerrifyAccessToken::class);
            $group->get('/by-team', GetMemberByTeam::class)->add(VerrifyAccessToken::class);
            $group->get('/my-information', GetMyInformation::class)->add(VerrifyAccessToken::class);
            $group->post('/update-password', UpdatePassword::class)->add(VerrifyAccessToken::class);
        });

        $group->group('/avatar', function (Group $group) {
            $group->post('/upload', UploadAvatar::class)->add(VerrifyAccessToken::class);
        });

        $group->group('/role', function (Group $group) {
            $group->get('/', GetRole::class)->add(VerrifyAccessToken::class);
        });

        $group->group('/setting', function (Group $group) {
            $group->group('/type', function (Group $group) {
                $group->get('/', TypeGetList::class)->add(VerrifyAccessToken::class);
                $group->post('/', TypeCreate::class)->add(VerrifyAccessToken::class);
                $group->put('/', TypeUpdate::class)->add(VerrifyAccessToken::class);
                $group->delete('/', TypeDelete::class)->add(VerrifyAccessToken::class);
            });
            $group->group('/location', function (Group $group) {
                $group->get('/', LocationGetList::class)->add(VerrifyAccessToken::class);
                $group->post('/', LocationCreate::class)->add(VerrifyAccessToken::class);
                $group->put('/', LocationUpdate::class)->add(VerrifyAccessToken::class);
                $group->delete('/', LocationDelete::class)->add(VerrifyAccessToken::class);
            });
            $group->group('/deflect', function (Group $group) {
                $group->get('/', DeflectGetList::class)->add(VerrifyAccessToken::class);
                $group->post('/', DeflectCreate::class)->add(VerrifyAccessToken::class);
                $group->put('/', DeflectUpdate::class)->add(VerrifyAccessToken::class);
                $group->delete('/', DeflectDelete::class)->add(VerrifyAccessToken::class);
            });
            $group->group('/system', function (Group $group) {
                $group->get('/', SystemGetList::class)->add(VerrifyAccessToken::class);
                $group->post('/', SystemCreate::class)->add(VerrifyAccessToken::class);
                $group->put('/', SystemUpdate::class)->add(VerrifyAccessToken::class);
                $group->delete('/', SystemDelete::class)->add(VerrifyAccessToken::class);
            });
        });

        $group->group('/project', function (Group $group) {
            $group->get('/', ProjectGetList::class)->add(VerrifyAccessToken::class);
            $group->get('/detail/{id}', ProjectGetDetail::class)->add(VerrifyAccessToken::class);
            $group->get('/checker-team/{id}', ProjectGetCheckerTeam::class)->add(VerrifyAccessToken::class);
            $group->get('/checker-list', ProjectGetCheckerList::class)->add(VerrifyAccessToken::class);
            $group->get('/file/{id}', ProjectGetFile::class)->add(VerrifyAccessToken::class);
            $group->post('/create', ProjectCreate::class)->add(VerrifyAccessToken::class);
            $group->post('/delete', ProjectDelete::class)->add(VerrifyAccessToken::class);
            $group->post('/add-teams', AddProjectTeams::class)->add(VerrifyAccessToken::class);
            $group->post('/delete-checker', DeleteTeamChecker::class)->add(VerrifyAccessToken::class);
            $group->group('/edit', function (Group $group) {
                $group->post('/project-name', EditProjectName::class)->add(VerrifyAccessToken::class);
                $group->post('/project-note', EditProjectNote::class)->add(VerrifyAccessToken::class);
                $group->post('/type-address', EditTypeAddress::class)->add(VerrifyAccessToken::class);
                $group->post('/type-usable-area', EditTypeUsableArea::class)->add(VerrifyAccessToken::class);
                $group->post('/customer-name', EditCustomerName::class)->add(VerrifyAccessToken::class);
                $group->post('/customer-phone', EditCustomerPhone::class)->add(VerrifyAccessToken::class);
                $group->post('/customer-email', EditCustomerEmail::class)->add(VerrifyAccessToken::class);
                $group->post('/coordinator-name', EditCoordinatorName::class)->add(VerrifyAccessToken::class);
                $group->post('/coordinator-phone', EditCoordinatorPhone::class)->add(VerrifyAccessToken::class);
                $group->post('/coordinator-email', EditCoordinatorEmail::class)->add(VerrifyAccessToken::class);
            });
            $group->group('/delete', function (Group $group) {
                $group->post('/file', FileDeleteImage::class)->add(VerrifyAccessToken::class);
            });
            $group->group('/upload', function (Group $group) {
                $group->post('/file', FileUploadImage::class)->add(VerrifyAccessToken::class);
            });
            $group->group('/inspection', function (Group $group) {
                $group->post('/list', InspcetionGetList::class)->add(VerrifyAccessToken::class);
                $group->post('/detail', InspcetionGetDetail::class)->add(VerrifyAccessToken::class);
                $group->post('/create', InspcetionCreateDetail::class)->add(VerrifyAccessToken::class);
                $group->post('/delete', InspcetionDelete::class)->add(VerrifyAccessToken::class);
                $group->post('/before-storage', InspcetionDeleteBeforeStorage::class)->add(VerrifyAccessToken::class);
                $group->post('/delete-storage', InspcetionDeleteStorage::class)->add(VerrifyAccessToken::class);
                $group->post('/delete-storage-group', InspcetionDeleteStorageGroup::class)->add(VerrifyAccessToken::class);
                $group->group('/location', function (Group $group) {
                    $group->post('/list', InspcetionGetLocationList::class)->add(VerrifyAccessToken::class);
                    $group->post('/create', InspcetionCreateLocation::class)->add(VerrifyAccessToken::class);
                    $group->post('/delete', InspcetionDeleteLocation::class)->add(VerrifyAccessToken::class);
                    $group->post('/edit', InspcetionEditLocation::class)->add(VerrifyAccessToken::class);
                    $group->post('/detail', InspcetionGetLocationDetail::class)->add(VerrifyAccessToken::class);
                    $group->group('/deflect', function (Group $group) {
                        $group->post('/list', InspcetionGetLocationDeflectList::class)->add(VerrifyAccessToken::class);
                        $group->post('/create', InspcetionCreateLocationDeflect::class)->add(VerrifyAccessToken::class);
                        $group->post('/delete', InspcetionGetLocationDeflectDelete::class)->add(VerrifyAccessToken::class);
                        $group->post('/status', InspcetionEditLocationDeflectStatus::class)->add(VerrifyAccessToken::class);
                        $group->post('/detail', InspcetionEditLocationDeflectDetail::class)->add(VerrifyAccessToken::class);
                        $group->post('/storage-list', InspcetionGetLocationDeflectStorage::class)->add(VerrifyAccessToken::class);
                    });
                });
                $group->group('/system', function (Group $group) {
                    $group->post('/list', InspcetionGetSystemList::class)->add(VerrifyAccessToken::class);
                    $group->post('/create', InspcetionCreateSystem::class)->add(VerrifyAccessToken::class);
                    $group->post('/delete', InspcetionDeleteSystem::class)->add(VerrifyAccessToken::class);
                    $group->post('/edit', InspcetionEditSystem::class)->add(VerrifyAccessToken::class);
                    $group->post('/detail', InspcetionGetSystemDetail::class)->add(VerrifyAccessToken::class);
                    $group->group('/deflect', function (Group $group) {
                        $group->post('/list', InspcetionGetSystemDeflectList::class)->add(VerrifyAccessToken::class);
                        $group->post('/create', InspcetionCreateSystemDeflect::class)->add(VerrifyAccessToken::class);
                        $group->post('/delete', InspcetionGetSystemDeflectDelete::class)->add(VerrifyAccessToken::class);
                        $group->post('/status', InspcetionEditSystemDeflectStatus::class)->add(VerrifyAccessToken::class);
                        $group->post('/detail', InspcetionEditSystemDeflectDetail::class)->add(VerrifyAccessToken::class);
                        $group->post('/storage-list', InspcetionGetSystemDeflectStorage::class)->add(VerrifyAccessToken::class);
                    });
                });
                $group->group('/storage', function (Group $group) {
                    $group->post('/list', InspcetionGetImageList::class)->add(VerrifyAccessToken::class);
                    $group->post('/upload', InspcetionUploadImage::class)->add(VerrifyAccessToken::class);
                    $group->post('/delete', InspcetionDeleteImage::class)->add(VerrifyAccessToken::class);
                    $group->post('/edit-name', InspcetionEditImageName::class)->add(VerrifyAccessToken::class);
                });
                $group->group('/report', function (Group $group) {
                    $group->get('/', InspectionGetReport::class)->add(VerrifyAccessToken::class);
                    $group->post('/create', InspectionCreateReport::class)->add(VerrifyAccessToken::class);
                    $group->post('/delete', InspectionDeleteReport::class)->add(VerrifyAccessToken::class);
                    $group->post('/detail', InspectionGetReportDetail::class)->add(VerrifyAccessToken::class);
                    $group->post('/approval', InspectionReportStatusApproval::class)->add(VerrifyAccessToken::class);
                    $group->post('/approval-cancel', InspectionReportStatusApprovalCancel::class)->add(VerrifyAccessToken::class);
                    $group->post('/approved', InspectionReportStatusApproved::class)->add(VerrifyAccessToken::class);
                    $group->post('/image-64', InspectionReportGetImageBase64::class)->add(VerrifyAccessToken::class);
                    $group->post('/save-pdf', InspectionReportSavePDF::class)->add(VerrifyAccessToken::class);
                    $group->group('/note', function (Group $group) {
                        $group->post('/note-list', InspectionReportGetNoteList::class)->add(VerrifyAccessToken::class);
                        $group->post('/add', InspectionReportAddNote::class)->add(VerrifyAccessToken::class);
                        $group->post('/edit', InspectionReportEditNote::class)->add(VerrifyAccessToken::class);
                        $group->post('/delete', InspectionReportDeleteNote::class)->add(VerrifyAccessToken::class);
                    });
                    $group->group('/location', function (Group $group) {
                        $group->post('/list', InspectionReportGetLocationList::class)->add(VerrifyAccessToken::class);
                    });
                    $group->group('/system', function (Group $group) {
                        $group->post('/list', InspectionReportGetSystemList::class)->add(VerrifyAccessToken::class);
                    });
                });
            });
        });
    });
};
