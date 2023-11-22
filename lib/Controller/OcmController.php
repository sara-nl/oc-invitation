<?php

/**
 * OCM controller
 */

namespace OCA\Invitation\Controller;

use OCA\Invitation\AppInfo\InvitationApp;
use OCA\Invitation\AppInfo\AppError;
use OCA\Invitation\Db\Schema;
use OCA\Invitation\Federation\Invitation;
use OCA\Invitation\Service\InvitationService;
use OCA\Invitation\Service\NotFoundException;
use OCA\Invitation\Service\ServiceException;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataResponse;
use OCP\ILogger;
use OCP\IRequest;

/**
 * Class OcmController.
 * Enhances the existing federatedfilesharing app with the ocm endpoint '/invite-accepted'
 *
 */
class OcmController extends Controller
{
    private InvitationService $invitationService;
    private ILogger $logger;

    public function __construct($appName, IRequest $request, InvitationService $invitationService)
    {
        parent::__construct($appName, $request);
        $this->invitationService = $invitationService;
        $this->logger = \OC::$server->getLogger();
    }

    /**
     * Inform the sender of the invite that it has been accepted by the recipient.
     *
     * FIXME: use method parameters
     *
     * @NoCSRFRequired
     * @PublicPage
     * @param string $recipientProvider maps to recipient_domain in the Invitation entity
     * @param string $token the invite token
     * @param string $userID the recipient cloud ID
     * @param string $email the recipient email
     * @param string $name the recipient name
     * @return DataResponse
     */
    public function inviteAccepted(
        string $recipientProvider = '',
        string $token = '',
        string $userID = '',
        string $email = '',
        string $name = ''
    ): DataResponse {
        if ($recipientProvider == '') {
            return new DataResponse(
                [
                    'success' => false,
                    'error_message' => 'recipient provider missing'
                ],
                Http::STATUS_NOT_FOUND
            );
        }
        if ($token == '') {
            return new DataResponse(
                [
                    'success' => false,
                    'error_message' => 'sender token missing'
                ],
                Http::STATUS_NOT_FOUND
            );
        }
        if ($userID == '') {
            return new DataResponse(
                [
                    'success' => false,
                    'error_message' => 'recipient user ID missing'
                ],
                Http::STATUS_NOT_FOUND
            );
        }
        if ($email == '') {
            return new DataResponse(
                [
                    'success' => false,
                    'error_message' => 'recipient email missing'
                ],
                Http::STATUS_NOT_FOUND
            );
        }
        if ($name == '') {
            return new DataResponse(
                [
                    'success' => false,
                    'error_message' => 'recipient name missing'
                ],
                Http::STATUS_NOT_FOUND
            );
        }

        $invitation = null;
        try {
            $invitation = $this->invitationService->findByToken($token, false);

            // check if the receiver has not already accepted a previous invitation
            $existingInvitations = $this->invitationService->findAll([
                [Schema::VINVITATION_SENDER_CLOUD_ID => $invitation->getSenderCloudId()],
                [Schema::VINVITATION_RECIPIENT_CLOUD_ID => $userID],
                [Schema::VINVITATION_STATUS => Invitation::STATUS_ACCEPTED],
                [Schema::VINVITATION_STATUS => Invitation::STATUS_REVOKED],
            ], false);
            if (count($existingInvitations) > 0) {
                $this->logger->error("Invitation for remote user with name '$name' has been revoked or already accepted.", ['app' => InvitationApp::APP_NAME]);
                return new DataResponse(
                    [
                        'success' => false,
                        'error_message' => AppError::OCM_INVITE_ACCEPTED_EXISTS
                    ],
                    Http::STATUS_NOT_FOUND
                );
            }
        } catch (NotFoundException $e) {
            $this->logger->error($e->getMessage() . ' Stacktrace: ' . $e->getTraceAsString(), ['app' => InvitationApp::APP_NAME]);
            return new DataResponse(
                [
                    'success' => false,
                    'error_message' => AppError::OCM_INVITE_ACCEPTED_NOT_FOUND
                ],
                Http::STATUS_NOT_FOUND
            );
        } catch (ServiceException $e) {
            $this->logger->error($e->getMessage() . ' Stacktrace: ' . $e->getTraceAsString(), ['app' => InvitationApp::APP_NAME]);
            return new DataResponse(
                [
                    'success' => false,
                    'error_message' => AppError::OCM_INVITE_ACCEPTED_ERROR
                ],
                Http::STATUS_NOT_FOUND
            );
        }

        // update the invitation with the receiver's info
        $updateResult = $this->invitationService->update([
            Schema::VINVITATION_TOKEN => $invitation->getToken(),
            Schema::VINVITATION_RECIPIENT_DOMAIN => $recipientProvider,
            Schema::VINVITATION_RECIPIENT_CLOUD_ID => $userID,
            Schema::VINVITATION_RECIPIENT_EMAIL => $email,
            Schema::VINVITATION_RECIPIENT_NAME => $name,
            Schema::VINVITATION_STATUS => Invitation::STATUS_ACCEPTED,
        ], false);
        if ($updateResult == false) {
            $this->logger->error("Update failed for invitation with token '$token'", ['app' => InvitationApp::APP_NAME]);
            return new DataResponse(
                [
                    'success' => false,
                    'error_message' => AppError::OCM_INVITE_ACCEPTED_ERROR
                ],
                Http::STATUS_NOT_FOUND
            );
        }

        // TODO: at this point a notification could(should?) be created to inform the sender that the invite has been accepted.

        return new DataResponse(
            [
                'userID' => $invitation->getSenderCloudId(),
                'email' => $invitation->getSenderEmail(),
                'name' => $invitation->getSenderName(),
            ],
            Http::STATUS_OK
        );
    }
}
