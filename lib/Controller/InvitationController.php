<?php

/**
 * Invitation controller.
 *
 */

namespace OCA\Invitation\Controller;

use DateTime;
use Exception;
use OC\Mail\Mailer;
use OCA\Invitation\AppInfo\InvitationApp;
use OCA\Invitation\AppInfo\AppError;
use OCA\Invitation\Db\Schema;
use OCA\Invitation\Federation\Invitation;
use OCA\Invitation\Federation\Service\MeshRegistryService;
use OCA\Invitation\HttpClient;
use OCA\Invitation\Service\InvitationService;
use OCA\Invitation\Service\NotFoundException;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Http\RedirectResponse;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\ILogger;
use OCP\IRequest;
use Ramsey\Uuid\Uuid;

class InvitationController extends Controller
{
    private MeshRegistryService $meshRegistryService;
    private InvitationService $invitationService;
    private ILogger $logger;

    public function __construct(
        $appName,
        IRequest $request,
        MeshRegistryService $meshRegistryService,
        InvitationService $invitationService
    ) {
        parent::__construct($appName, $request);
        $this->meshRegistryService = $meshRegistryService;
        $this->invitationService = $invitationService;
        $this->logger = \OC::$server->getLogger();
    }

    /**
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     * @return TemplateResponse
     */
    public function index(): TemplateResponse
    {
        return new TemplateResponse($this->appName, 'invitation.index');
    }

    /**
     * Generates an invite and sends it to the specified email address.
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     * @param string email the email address to send the invite to
     * @param string senderName the name of the sender
     * @param string message the message for the receiver
     * @return DataResponse the result
     */
    public function generateInvite(string $email = '', string $message = ''): DataResponse
    {
        if ('' == $email) {
            return new DataResponse(
                [
                    'success' => false,
                    'error_message' => AppError::CREATE_INVITATION_NO_RECIPIENT_EMAIL,
                ],
                Http::STATUS_NOT_FOUND
            );
        }
        // generate the token
        $token = Uuid::uuid4();

        $params = [
            MeshRegistryService::PARAM_NAME_TOKEN => $token,
            MeshRegistryService::PARAM_NAME_PROVIDER_DOMAIN => $this->meshRegistryService->getDomain(),
            MeshRegistryService::PARAM_NAME_NAME => \OC::$server->getUserSession()->getUser()->getDisplayName()
        ];

        // Check for existing open and accepted invitations for the same recipient email
        // Note that accepted invitations might have another recipient's email set, so there might still already be an existing invitation
        // but this will be dealt with upon acceptance of this new invitation
        try {
            $fieldsAndValues = [];
            array_push($fieldsAndValues, [Schema::INVITATION_SENDER_CLOUD_ID => \OC::$server->getUserSession()->getUser()->getCloudId()]);
            array_push($fieldsAndValues, [Schema::INVITATION_RECIPIENT_EMAIL => $email]);
            array_push($fieldsAndValues, [Schema::INVITATION_STATUS => Invitation::STATUS_OPEN]);
            array_push($fieldsAndValues, [Schema::INVITATION_STATUS => Invitation::STATUS_ACCEPTED]);

            $invitations = $this->invitationService->findAll($fieldsAndValues);
            if (count($invitations) > 0) {
                return new DataResponse(
                    [
                        'success' => false,
                        'error_message' => AppError::CREATE_INVITATION_EXISTS,
                    ],
                    Http::STATUS_NOT_FOUND,
                );
            }
        } catch (Exception $e) {
            return new DataResponse(
                [
                    'success' => false,
                    'error_message' => AppError::CREATE_INVITATION_ERROR,
                ],
                Http::STATUS_NOT_FOUND,
            );
        }

        $inviteLink = $this->meshRegistryService->inviteLink($params);

        // persist the invite to send
        $invitation = new Invitation();
        $invitation->setUserCloudId(\OC::$server->getUserSession()->getUser()->getCloudId());
        $invitation->setToken($token);
        $invitation->setProviderDomain($this->meshRegistryService->getDomain());
        $invitation->setSenderCloudId(\OC::$server->getUserSession()->getUser()->getCloudId());
        $invitation->setSenderEmail(\OC::$server->getUserSession()->getUser()->getEMailAddress());
        $invitation->setRecipientEmail($email);
        $invitation->setSenderName(\OC::$server->getUserSession()->getUser()->getDisplayName());
        $invitation->setTimestamp(time());
        $invitation->setStatus(Invitation::STATUS_NEW);

        // TODO: send an email with the invitation link to the recipient ($email)
        //       note that the status of the invitation should change to 'invalid' in case of failure
        // $mailer = \OC::$server->getMailer();
        // $message = $mailer->createMessage();
        // $message->setSubject('Your Subject');
        // $message->setFrom(array('cloud@domain.org' => 'ownCloud Notifier'));
        // $message->setTo(array('recipient@domain.org' => 'Recipient'));
        // $message->setBody('The message text');
        // $mailer->send($message);
        // $mailer = new Mailer();

        // This message can then be passed to send() of \OC\Mail\Mailer

        // when all's well set status to open and persist
        $invitation->setStatus(Invitation::STATUS_OPEN);
        try {
            $newInvitation = $this->invitationService->insert($invitation);
        } catch (Exception $e) {
            $this->logger->error('An error occurred while generating the invite: ' . $e->getMessage(), ['app' => InvitationApp::APP_NAME]);
            return new DataResponse(
                [
                    'success' => false,
                    'error_message' => AppError::CREATE_INVITATION_ERROR,
                ],
                Http::STATUS_NOT_FOUND
            );
        }

        if (isset($newInvitation) && $invitation->getId() > 0) {
            return new DataResponse(
                [
                    'success' => true,
                    // FIXME: this will not be needed when the link is actually send by email
                    'message' => "The invite $inviteLink has been send to $email"
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
    public function handleInvite(string $token = '', string $providerDomain = '', string $name = ''): RedirectResponse
    {
        $urlGenerator = \OC::$server->getURLGenerator();

        if ($token == '') {
            \OC::$server->getLogger()->error('Invite is missing the token.', ['app' => InvitationApp::APP_NAME]);
            return new RedirectResponse($urlGenerator->linkToRoute(InvitationApp::APP_NAME . '.error.invitation', ['message' => AppError::HANDLE_INVITATION_ERROR]));
        }
        if ($providerDomain == '') {
            \OC::$server->getLogger()->error('Invite is missing the provider domain.', ['app' => InvitationApp::APP_NAME]);
            return new RedirectResponse($urlGenerator->linkToRoute(InvitationApp::APP_NAME . '.error.invitation', ['message' => AppError::HANDLE_INVITATION_ERROR]));
        }
        if ($name == '') {
            \OC::$server->getLogger()->error('Invite is missing sender name.', ['app' => InvitationApp::APP_NAME]);
            return new RedirectResponse($urlGenerator->linkToRoute(InvitationApp::APP_NAME . '.error.invitation', ['message' => AppError::HANDLE_INVITATION_ERROR]));
        }

        if (!$this->meshRegistryService->isKnowDomainProvider($providerDomain)) {
            \OC::$server->getLogger()->error('Provider domain is unknown.', ['app' => InvitationApp::APP_NAME]);
            return new RedirectResponse($urlGenerator->linkToRoute(InvitationApp::APP_NAME . '.error.invitation', ['message' => AppError::HANDLE_INVITATION_ERROR]));
        }

        // check if invitation doesn't already exists
        try {
            $invitation = $this->invitationService->findByToken($token);
            // we want a NotFoundException
            return new RedirectResponse($urlGenerator->linkToRoute(InvitationApp::APP_NAME . '.error.invitation', ['message' => AppError::HANDLE_INVITATION_EXISTS]));
        } catch (NotFoundException $e) {
            // we're good to go
        } catch (Exception $e) {
            $this->logger->error($e->getMessage(), ['app' => InvitationApp::APP_NAME]);
            return new RedirectResponse($urlGenerator->linkToRoute(InvitationApp::APP_NAME . '.error.invitation', ['message' => AppError::HANDLE_INVITATION_ERROR]));
        }

        // persist the received invite
        $invitation = new Invitation();
        $invitation->setUserCloudId(\OC::$server->getUserSession()->getUser()->getCloudId());
        $invitation->setToken($token);
        $invitation->setProviderDomain($providerDomain);
        $invitation->setSenderName($name);
        $invitation->setRecipientDomain($this->meshRegistryService->getDomain());
        $invitation->setRecipientCloudId(\OC::$server->getUserSession()->getUser()->getCloudId());
        $invitation->setTimestamp(time());
        $invitation->setStatus(Invitation::STATUS_OPEN);
        try {
            $this->invitationService->insert($invitation);
        } catch (Exception $e) {
            $this->logger->error($e->getMessage(), ['app' => InvitationApp::APP_NAME]);
            return new RedirectResponse($urlGenerator->linkToRoute(InvitationApp::APP_NAME . '.error.invitation', ['message' => AppError::HANDLE_INVITATION_ERROR]));
        }

        $manager = \OC::$server->getNotificationManager();
        $notification = $manager->createNotification();

        $acceptAction = $notification->createAction();
        $acceptAction
            ->setLabel('accept')
            ->setLink("/apps/" . InvitationApp::APP_NAME . "/accept-invite?token=$token", 'GET');

        $declineAction = $notification->createAction();
        $declineAction->setLabel('decline')
            ->setLink('/apps/' . InvitationApp::APP_NAME . "/#", 'GET');

        $notification->setApp(InvitationApp::APP_NAME)
            // the user that has received the invite is logged in at this point
            ->setUser(\OC::$server->getUserSession()->getUser()->getUID())
            ->setDateTime(new DateTime())
            ->setObject(MeshRegistryService::PARAM_NAME_TOKEN, $token)
            ->setSubject('invitation', [
                MeshRegistryService::PARAM_NAME_TOKEN => $token,
                MeshRegistryService::PARAM_NAME_PROVIDER_DOMAIN => $providerDomain,
                MeshRegistryService::PARAM_NAME_NAME => $name,
            ])
            ->addAction($acceptAction)
            ->addAction($declineAction);

        $manager->notify($notification);

        return new RedirectResponse($urlGenerator->linkToRoute('files.view.index'));
    }

    /**
     * Notify the sender of the invite that we accept it and include our user info.
     * The response should contain the sender's info which we will persist together with the invite.
     * And at that point the invitation flow has successfully completed.
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
                ['error_message' => 'sender token missing'],
                Http::STATUS_NOT_FOUND
            );
        }

        $invitation = null;
        try {
            $invitation = $this->invitationService->findByToken($token);
        } catch (NotFoundException $e) {
            return new DataResponse(
                ['error_message' => 'acceptInvite failed'],
                Http::STATUS_NOT_FOUND
            );
        }

        $recipientDomain = $this->meshRegistryService->getDomain();
        $recipientCloudID = \OC::$server->getUserSession()->getUser()->getCloudId();
        $recipientEmail = \OC::$server->getUserSession()->getUser()->getEMailAddress();
        $recipientName = \OC::$server->getUserSession()->getUser()->getDisplayName();
        $params = [
            MeshRegistryService::PARAM_NAME_RECIPIENT_PROVIDER => $recipientDomain,
            MeshRegistryService::PARAM_NAME_TOKEN => $token,
            MeshRegistryService::PARAM_NAME_USER_ID => $recipientCloudID,
            MeshRegistryService::PARAM_NAME_EMAIL => $recipientEmail,
            MeshRegistryService::PARAM_NAME_NAME => $recipientName,
        ];

        $url = $this->meshRegistryService->getFullInviteAcceptedEndpointURL($invitation->getProviderDomain());
        $httpClient = new HttpClient();
        $response = $httpClient->curlPost($url, $params);
        $resArray = (array)$response['response'];

        if (
            $response['success'] == false
            || $this->verifiedInviteAcceptedResponse($resArray) == false
        ) {
            $this->logger->error('Failed to accept the invitation: /invite-accepted failed with response: ' . print_r($response, true), ['app' => InvitationApp::APP_NAME]);
            return new DataResponse(
                ['error_message' => 'Failed to accept the invitation'],
                Http::STATUS_NOT_FOUND
            );
        }

        // check if there is not already an accepted invitation forremote user, and if there is decline this invitation

        // all's well, update the invitation
        $updateResult = $this->invitationService->update(
            [
                Schema::ID => $invitation->getId(),
                Schema::INVITATION_RECIPIENT_DOMAIN => $recipientDomain,
                Schema::INVITATION_RECIPIENT_EMAIL => $recipientEmail,
                Schema::INVITATION_RECIPIENT_NAME => $recipientName,
                Schema::INVITATION_SENDER_CLOUD_ID => $resArray[MeshRegistryService::PARAM_NAME_USER_ID],
                Schema::INVITATION_SENDER_EMAIL => $resArray[MeshRegistryService::PARAM_NAME_EMAIL],
                Schema::INVITATION_SENDER_NAME => $resArray[MeshRegistryService::PARAM_NAME_NAME],
                Schema::INVITATION_STATUS => Invitation::STATUS_ACCEPTED,
            ],
            true
        );
        if ($updateResult == false) {
            $this->logger->error("Failed to handle /accept-invite (invitation with token '$token' could not be updated).", ['app' => InvitationApp::APP_NAME]);
            return new DataResponse(
                [
                    'success' => false,
                    'error_message' => 'Failed to accept the invitation'
                ],
                Http::STATUS_NOT_FOUND
            );
        }

        try {
            // finally remove the notification
            $manager = \OC::$server->getNotificationManager();
            $notification = $manager->createNotification();
            $notification
                ->setApp(InvitationApp::APP_NAME)
                ->setUser(\OC::$server->getUserSession()->getUser()->getUID())
                ->setObject(MeshRegistryService::PARAM_NAME_TOKEN, $token);
            $manager->markProcessed($notification);
        } catch (Exception $e) {
            // invitation has already successfully been accepted; we only log this exception
            $this->logger->error("Unable to remove notification for app '" . InvitationApp::APP_NAME . "' user '" . \OC::$server->getUserSession()->getUser()->getUID() . "' and token '$token'.", ['app' => InvitationApp::APP_NAME]);
        }

        return new DataResponse(
            [
                'success' => true
            ],
            Http::STATUS_OK
        );
    }

    /**
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @param string $token the token of the invite we want to decline
     * @return DataResponse
     */
    public function declineInvite(string $token = ''): DataResponse
    {
        try {
            $invitation = $this->invitationService->findByToken($token);

            $updateResult = $this->invitationService->update([
                Schema::ID => $invitation->getId(),
                Schema::INVITATION_STATUS => Invitation::STATUS_DECLINED,
            ]);

            if ($updateResult == true) {
                // remove notification
                $manager = \OC::$server->getNotificationManager();
                $notification = $manager->createNotification();
                $notification
                    ->setApp(InvitationApp::APP_NAME)
                    ->setUser(\OC::$server->getUserSession()->getUser()->getUID())
                    ->setObject(MeshRegistryService::PARAM_NAME_TOKEN, $token);
                $manager->markProcessed($notification);

                return new DataResponse(
                    [
                        'success' => true,
                    ],
                    Http::STATUS_OK,
                );
            }
            return new DataResponse(
                [
                    'success' => false,
                ],
                Http::STATUS_NOT_FOUND,
            );
        } catch (NotFoundException $e) {
            return new DataResponse(
                [
                    'success' => false,
                    'error_message' => AppError::INVITATION_NOT_FOUND,
                ],
                Http::STATUS_NOT_FOUND,
            );
        } catch (Exception $e) {
            return new DataResponse(
                [
                    'success' => false,
                    'error_message' => AppError::ERROR,
                ],
                Http::STATUS_NOT_FOUND,
            );
        }
    }

    /**
     * Verify the /invite-accepted response for all required fields.
     *
     * @param array $response the response to verify
     * @return bool true if the response is valid, false otherwise
     */
    private function verifiedInviteAcceptedResponse(array $response): bool
    {
        if (!isset($response) || $response[MeshRegistryService::PARAM_NAME_USER_ID] == '') {
            $this->logger->error('/invite-accepted response does not contain the user id of the sender of the invitation.');
            return false;
        }
        if (!isset($response[MeshRegistryService::PARAM_NAME_EMAIL]) || $response[MeshRegistryService::PARAM_NAME_EMAIL] == '') {
            $this->logger->error('/invite-accepted response does not contain the email of the sender of the invitation.');
            return false;
        }
        if (!isset($response[MeshRegistryService::PARAM_NAME_NAME]) || $response[MeshRegistryService::PARAM_NAME_NAME] == '') {
            $this->logger->error('/invite-accepted response does not contain the name of the sender of the invitation.');
            return false;
        }
        return true;
    }

    /**
     *
     * @NoCSRFRequired
     */
    public function find(int $id = null): DataResponse
    {
        if (!isset($id)) {
            return new DataResponse(
                [
                    'success' => false,
                    'error_message' => AppError::REQUEST_MISSING_PARAMETER,
                ],
                Http::STATUS_NOT_FOUND,
            );
        }
        try {
            $invitation = $this->invitationService->find($id);
            return new DataResponse(
                [
                    'success' => true,
                    'invitation' => $invitation,
                ]
            );
        } catch (NotFoundException $e) {
            return new DataResponse(
                [
                    'success' => false,
                    'error_message' => AppError::INVITATION_NOT_FOUND,
                ],
                Http::STATUS_NOT_FOUND,
            );
        }
    }

    /**
     * example url: https://rd-1.nl/apps/invitation/find-all-invitations?fields=[{"status":"open"},{"status":"accepted"}]
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function findAll(string $fields = null): DataResponse
    {
        if (!isset($fields)) {
            return new DataResponse(
                [
                    'success' => false,
                    'error_message' => AppError::REQUEST_MISSING_PARAMETER,
                ],
                Http::STATUS_NOT_FOUND,
            );
        }
        try {
            $fieldsAndValues = json_decode($fields, true);
            $invitations = $this->invitationService->findAll($fieldsAndValues);
            return new DataResponse(
                [
                    'success' => true,
                    'invitations' => $invitations,
                ],
                Http::STATUS_OK
            );
        } catch (Exception $e) {
            return new DataResponse(
                [
                    'success' => false,
                    'error_message' => AppError::ERROR,
                ],
                Http::STATUS_NOT_FOUND,
            );
        }
    }

    /**
     *
     * @NoCSRFRequired
     */
    public function findByToken(string $token = null): DataResponse
    {
        if (!isset($token)) {
            return new DataResponse(
                [
                    'success' => false,
                    'error_message' => AppError::REQUEST_MISSING_PARAMETER,
                ],
                Http::STATUS_NOT_FOUND,
            );
        }
        try {
            $invitation = $this->invitationService->findByToken($token);
            return new DataResponse(
                [
                    'success' => true,
                    'invitation' => $invitation,
                ],
                Http::STATUS_OK,
            );
        } catch (NotFoundException $e) {
            $this->logger->error($e->getMessage(), ['app' => InvitationApp::APP_NAME]);
            return new DataResponse(
                [
                    'success' => false,
                    'error_message' => AppError::INVITATION_NOT_FOUND,
                ],
                Http::STATUS_NOT_FOUND,
            );
        } catch (Exception $e) {
            $this->logger->error($e->getMessage(), ['app' => InvitationApp::APP_NAME]);
            return new DataResponse(
                [
                    'success' => false,
                    'error_message' => AppError::ERROR,
                ],
                Http::STATUS_NOT_FOUND,
            );
        }
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function update(int $id, string $status): DataResponse
    {
        $fieldsAndValues = [];
        if (isset($id)) {
            $fieldsAndValues['id'] = $id;
        }
        if (isset($status)) {
            $fieldsAndValues[Schema::INVITATION_STATUS] = $status;
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
