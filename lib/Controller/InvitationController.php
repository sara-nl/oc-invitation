<?php

/**
 * 
 */

namespace OCA\RDMesh\Controller;

use DateTime;
use Exception;
use OC;
use OCA\RDMesh\AppInfo\RDMesh;
use OCA\RDMesh\Db\Schema;
use OCA\RDMesh\Federation\Invitation;
use OCA\RDMesh\HttpClient;
use OCA\RDMesh\Service\InvitationService;
use OCA\RDMesh\Service\MeshService;
use OCA\RDMesh\Service\NotFoundException;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Http\RedirectResponse;
use OCP\ILogger;
use OCP\IRequest;
use Ramsey\Uuid\Uuid;

class InvitationController extends Controller
{

    private MeshService $meshService;
    private InvitationService $invitationService;
    private ILogger $logger;

    public function __construct(
        $appName,
        IRequest $request,
        MeshService $meshService,
        InvitationService $invitationService
    ) {
        parent::__construct($appName, $request);
        $this->meshService = $meshService;
        $this->invitationService = $invitationService;
        $this->logger = \OC::$server->getLogger();
    }

    /**
     * Generates an invite and sends it to the specified email address.
     * 
     * @NoAdminRequired
     * @NoCSRFRequired
     * @param string email the email address to send the invite to
     * @return DataResponse the result
     */
    // TODO: this call should be the result of a 'generate invite' form with all the relevant info.
    //       (sender name etc.)
    public function generateInvite(string $email = '', string $senderName = ''): DataResponse
    {
        if ('' == $email) {
            return new DataResponse(
                ['message' => 'You must provide the email address of the intended recipient of the invite.'],
                Http::STATUS_NOT_FOUND
            );
        }
        if ($senderName == '') {
            // FIXME: decide whether the user's _display_name_ should be used here
            return new DataResponse(
                ['message' => 'You must provide your name in order to generate an invite.'],
                Http::STATUS_NOT_FOUND
            );
        }

        // generate the token
        $token = Uuid::uuid4();

        // add the necessary parameters to the link
        // TODO: decide what parameters actually must/can be (savely) send
        $params = [
            MeshService::PARAM_NAME_TOKEN => $token,
            MeshService::PARAM_NAME_PROVIDER_DOMAIN => $this->meshService->getDomain(),
        ];

        $inviteLink = $this->meshService->inviteLink($params);

        // persist the invite to send
        $invitation = new Invitation();
        $invitation->setToken($token);
        $invitation->setProviderDomain($this->meshService->getDomain());
        $invitation->setSenderCloudId(OC::$server->getUserSession()->getUser()->getCloudId());
        $invitation->setSenderEmail(OC::$server->getUserSession()->getUser()->getEMailAddress());
        $invitation->setSenderName($senderName);
        $invitation->setTimestamp(time());
        $invitation->setStatus(Invitation::STATUS_NEW);

        // TODO: send an email with the invitation link to the recipient ($email)
        //       note that the status of the invitation should change to 'invalid' in case of failure

        // when all's well set status and persist
        $invitation->setStatus(Invitation::STATUS_OPEN);
        try {
            $newInvitation = $this->invitationService->insert($invitation);
        } catch (Exception $e) {
            $this->logger->error('An error occurred while generating the invite: ' . $e->getMessage(), ['app' => RDMesh::APP_NAME]);
            return new DataResponse(
                [
                    'error' => 'The invite could not be generated',
                ],
                Http::STATUS_NOT_FOUND
            );
        }

        if (isset($newInvitation) && $invitation->getId() > 0) {
            return new DataResponse(
                [
                    'message' => 'This invite has been send to ' . $email,
                    'inviteLink' => $inviteLink,
                ],
                Http::STATUS_OK
            );
        }
    }

    /**
     * Handle the invite by creating the notification with the option to accept or reject it.
     * 
     * @NoAdminRequired
     * @NoCSRFRequired
     * @param string $token the token
     * @param string $senderDomain the domain of the sender
     * @param string $senderEmail the email of the sender
     * @return RedirectResponse
     */
    public function handleInvite(string $token = '', string $providerDomain = ''): RedirectResponse
    {
        $urlGenerator = \OC::$server->getURLGenerator();

        if ($token == '') {
            \OC::$server->getLogger()->error('Invite is missing the token.', ['app' => RDMesh::APP_NAME]);
            return new RedirectResponse($urlGenerator->linkToRoute('rd-mesh.error.invitation', ['message' => 'The invitation is invalid.']));
        }
        if ($token == '') {
            \OC::$server->getLogger()->error('Invite is missing the provider domain.', ['app' => RDMesh::APP_NAME]);
            return new RedirectResponse($urlGenerator->linkToRoute('rd-mesh.error.invitation', ['message' => 'The invitation is invalid.']));
        }

        // persist the received invite
        $invitation = new Invitation();
        $invitation->setToken($token);
        $invitation->setProviderDomain($providerDomain);
        $invitation->setRecipientCloudId(\OC::$server->getUserSession()->getUser()->getCloudId());
        $invitation->setTimestamp(time());
        $invitation->setStatus(Invitation::STATUS_OPEN);
        $this->invitationService->insert($invitation);

        $manager = \OC::$server->getNotificationManager();
        $notification = $manager->createNotification();

        $acceptAction = $notification->createAction();
        $acceptAction
            ->setLabel('accept')
            ->setLink("/apps/rd-mesh/accept-invite?token=$token", 'GET');

        $declineAction = $notification->createAction();
        // TODO: implement /decline-invite
        $declineAction->setLabel('decline')
            ->setLink('/apps/rd-mesh/decline-invite', 'DELETE');

        $notification->setApp('notification-invite')
            // the user that has received the invite is logged in at this point
            ->setUser(OC::$server->getUserSession()->getUser()->getUID())
            ->setDateTime(new DateTime())
            // FIXME: find out on what object actually means
            ->setObject('providerDomain', $providerDomain)
            ->setSubject('invitation', [
                MeshService::PARAM_NAME_TOKEN => $token,
                MeshService::PARAM_NAME_PROVIDER_DOMAIN => $providerDomain,
            ])
            ->addAction($acceptAction)
            ->addAction($declineAction);

        $manager->notify($notification);

        return new RedirectResponse($urlGenerator->linkToRoute('files.view.index'));
    }

    /**
     * Notify the inviter that we accept the invite and include our user info.
     * The response should contain the inviter's info which we will persist together with the invite.
     * And at that point the invitation has successfully completed.
     * 
     * @NoAdminRequired
     * @NoCSRFRequired
     * @param string $token the token
     * @return DataResponse
     */
    public function acceptInvite(string $token = ''): DataResponse
    {
        if ($token == '') {
            return new DataResponse(
                ['error' => 'sender token missing'],
                Http::STATUS_NOT_FOUND
            );
        }

        $invitation = null;
        try {
            $invitation = $this->invitationService->findByToken($token);
        } catch (NotFoundException $e) {
            return new DataResponse(
                ['error' => 'acceptInvite failed', 'message' => $e->getMessage()],
                Http::STATUS_NOT_FOUND
            );
        }

        $recipientDomain = $this->meshService->getDomain();
        $recipientCloudID = \OC::$server->getUserSession()->getUser()->getCloudId();
        $recipientEmail = \OC::$server->getUserSession()->getUser()->getEMailAddress();
        $recipientName = \OC::$server->getUserSession()->getUser()->getDisplayName();
        $params = [
            MeshService::PARAM_NAME_RECIPIENT_PROVIDER => $recipientDomain,
            MeshService::PARAM_NAME_TOKEN => $token,
            MeshService::PARAM_NAME_USER_ID => $recipientCloudID,
            MeshService::PARAM_NAME_EMAIL => $recipientEmail,
            MeshService::PARAM_NAME_NAME => $recipientName,
        ];
        $url = $this->meshService->getFullInviteAcceptedEndpointURL($invitation->getProviderDomain());
        $httpClient = new HttpClient();
        $response = $httpClient->curlPost($url, $params);

        $logMessage = '';
        $inviteAccepted = true;

        if ($response['success'] == false) {
            $inviteAccepted = false;
            $logMessage = 'Failed to accept the invitation: /invite-accepted failed with response: ' . print_r($response, true);
        }

        $resArray = (array)$response['response'];
        if ($this->verifiedInviteAcceptedResponse($resArray) == false) {
            $inviteAccepted = false;
            $logMessage = 'Failed to accept the invitation: /invite-accepted response is invalid.';
        }

        // all's well, update the invitation
        $updateResult = $this->invitationService->update(
            [
                'id' => $invitation->getId(),
                Schema::Invitation_recipient_domain => $recipientDomain,
                Schema::Invitation_recipient_email => $recipientEmail,
                Schema::Invitation_recipient_name => $recipientName,
                Schema::Invitation_sender_cloud_id => $resArray[MeshService::PARAM_NAME_USER_ID],
                Schema::Invitation_sender_email => $resArray[MeshService::PARAM_NAME_EMAIL],
                Schema::Invitation_sender_name => $resArray[MeshService::PARAM_NAME_NAME],
                Schema::Invitation_status => Invitation::STATUS_ACCEPTED,
            ]
        );
        if ($updateResult == false) {
            $logMessage = "Failed to handle /accept-invite (invitation with token=$token could not be updated).";
        }

        if ($updateResult == true && $inviteAccepted == true) {
            return new DataResponse(
                [],
                Http::STATUS_OK
            );
        }

        $this->logger->error($logMessage, ['app' => RDMesh::APP_NAME]);
        return new DataResponse(
            ['error' => 'Failed to accept the invitation'],
            Http::STATUS_NOT_FOUND
        );
    }

    /**
     * Verify the /invite-accepted response for all required fields.
     * 
     * @param array $response the response to verify
     * @return bool true if the response is valid, false otherwise
     */
    private function verifiedInviteAcceptedResponse(array $response): bool
    {
        if (!isset($response) || $response[MeshService::PARAM_NAME_USER_ID] == '') {
            $this->logger->error('/invite-accepted response does not contain the user id of the sender of the invitation.');
            return false;
        }
        if (!isset($response[MeshService::PARAM_NAME_EMAIL]) || $response[MeshService::PARAM_NAME_EMAIL] == '') {
            $this->logger->error('/invite-accepted response does not contain the email of the sender of the invitation.');
            return false;
        }
        if (!isset($response[MeshService::PARAM_NAME_NAME]) || $response[MeshService::PARAM_NAME_NAME] == '') {
            $this->logger->error('/invite-accepted response does not contain the name of the sender of the invitation.');
            return false;
        }
        return true;
    }

    /**
     * 
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    // FIXME: remove this test endpoint
    public function find(int $id = null): DataResponse
    {
        if (isset($id)) {
            try {
                $invitation = $this->invitationService->find($id);
                return new DataResponse(
                    ['invitation' => $invitation],
                    Http::STATUS_OK,
                );
            } catch (NotFoundException $e) {
                return new DataResponse(
                    ['error' => $e->getMessage()],
                    Http::STATUS_NOT_FOUND,
                );
            }
        }
        return new DataResponse(
            ['error' => 'id is required'],
            Http::STATUS_NOT_FOUND,
        );
    }

    /**
     * 
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function findAll(string $fields): DataResponse
    {
        try {
            $fieldsAndValues = json_decode($fields, true);
            // $this->logger->debug(' - findAll params: ' . print_r($fieldsAndValues, true));
            $invitations = $this->invitationService->findAll($fieldsAndValues);
            return new DataResponse(
                [
                    'invitations' => $invitations,
                ],
                Http::STATUS_OK
            );
        } catch (Exception $e) {
            return new DataResponse(
                ['error' => 'An error has occurred.'],
                Http::STATUS_NOT_FOUND,
            );
        }
    }

    /**
     * 
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    // FIXME: remove this test endpoint
    public function findByToken(string $token = null): DataResponse
    {
        if (isset($token)) {
            try {
                $invitation = $this->invitationService->findByToken($token);
                return new DataResponse(
                    ['invitation' => print_r($invitation, true)],
                    Http::STATUS_OK,
                );
            } catch (NotFoundException $e) {
                return new DataResponse(
                    ['error' => $e->getMessage()],
                    Http::STATUS_NOT_FOUND,
                );
            }
        }
        return new DataResponse(
            ['error' => 'token is required'],
            Http::STATUS_NOT_FOUND,
        );
    }

    /**
     * 
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    // FIXME: remove this test endpoint
    public function update(int $id, string $status): DataResponse
    {
        $fieldsAndValues = [];
        if (isset($id)) {
            $fieldsAndValues['id'] = $id;
        }
        if (isset($status)) {
            $fieldsAndValues[Schema::Invitation_status] = $status;
        }

        $result = $this->invitationService->update($fieldsAndValues);

        if ($result === true) {
            return new DataResponse(
                ['result' => $result],
                Http::STATUS_OK,
            );
        }
        return new DataResponse(
            ['result' => $result],
            Http::STATUS_NOT_FOUND,
        );
    }
}
