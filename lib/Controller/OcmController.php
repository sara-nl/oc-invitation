<?php

/**
 * OCM controller
 */

namespace OCA\RDMesh\Controller;

use OCA\RDMesh\Db\Schema;
use OCA\RDMesh\Federation\Invitation;
use OCA\RDMesh\Service\InvitationService;
use OCA\RDMesh\Service\ServiceException;
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
                ['error' => 'recipient provider missing'],
                Http::STATUS_NOT_FOUND
            );
        }
        if ($token == '') {
            return new DataResponse(
                ['error' => 'sender token missing'],
                Http::STATUS_NOT_FOUND
            );
        }
        if ($userID == '') {
            return new DataResponse(
                ['error' => 'recipient user ID missing'],
                Http::STATUS_NOT_FOUND
            );
        }
        if ($email == '') {
            return new DataResponse(
                ['error' => 'recipient email missing'],
                Http::STATUS_NOT_FOUND
            );
        }
        if ($name == '') {
            return new DataResponse(
                ['error' => 'recipient name missing'],
                Http::STATUS_NOT_FOUND
            );
        }

        $invitation = null;
        try {
            $invitation = $this->invitationService->findByToken($token);
            // check if the receiver has not already accepted a previous invitation
            $existingInvitations = $this->invitationService->findAll([
                Schema::Invitation_sender_cloud_id => $invitation->getSenderCloudId(),
                Schema::Invitation_recipient_cloud_id => $userID,
                Schema::Invitation_status => Invitation::STATUS_ACCEPTED,
            ]);
            if (count($existingInvitations) > 0) {
                return new DataResponse(
                    ['error' => '/invite-accepted failed', 'message' => 'An accepted invitation already exists.'],
                    Http::STATUS_NOT_FOUND
                );
            }
        } catch (ServiceException $e) {
            return new DataResponse(
                ['error' => '/invite-accepted failed', 'message' => $e->getMessage()],
                Http::STATUS_NOT_FOUND
            );
        }

        // update the invitation with the receiver's info
        $updateResult = $this->invitationService->update([
            'id' => $invitation->getId(),
            Schema::Invitation_recipient_domain => $recipientProvider,
            Schema::Invitation_recipient_cloud_id => $userID,
            Schema::Invitation_recipient_email => $email,
            Schema::Invitation_recipient_name => $name,
            Schema::Invitation_status => Invitation::STATUS_ACCEPTED,
        ]);
        if ($updateResult == false) {
            return new DataResponse(
                [
                    'message' => 'Failed to handle /invite-accepted'
                ],
                Http::STATUS_NOT_FOUND
            );
        }

        // TODO: at this point a notification could/should be created to inform the sender that the invite has been accepted. 

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
