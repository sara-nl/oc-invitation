<?php

/**
 * Invitation controller.
 *
 */

namespace OCA\Invitation\Controller;

use DateTime;
use Exception;
use OCA\Invitation\AppInfo\InvitationApp;
use OCA\Invitation\AppInfo\AppError;
use OCA\Invitation\Db\Schema;
use OCA\Invitation\Federation\Invitation;
use OCA\Invitation\HttpClient;
use OCA\Invitation\Service\ApplicationConfigurationException;
use OCA\Invitation\Service\InvitationService;
use OCA\Invitation\Service\MeshRegistry\MeshRegistryService;
use OCA\Invitation\Service\NotFoundException;
use OCA\Invitation\Service\ServiceException;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Http\RedirectResponse;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IL10N;
use OCP\ILogger;
use OCP\IRequest;
use OCP\Template;
use OCP\Util;
use Ramsey\Uuid\Uuid;

class InvitationController extends Controller
{
    private MeshRegistryService $meshRegistryService;
    private InvitationService $invitationService;
    private IL10N $il10n;
    private ILogger $logger;

    public function __construct(
        $appName,
        IRequest $request,
        MeshRegistryService $meshRegistryService,
        InvitationService $invitationService,
        IL10N $il10n
    ) {
        parent::__construct($appName, $request);
        $this->meshRegistryService = $meshRegistryService;
        $this->invitationService = $invitationService;
        $this->il10n = $il10n;
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
     * @PublicPage
     * @NoCSRFRequired
     */
    public function invitation(string $token): DataResponse
    {
        $data = [];
        try {
            if (!Uuid::isValid($token)) {
                throw new NotFoundException("Token $token is invalid.");
            }
            $invitation = $this->invitationService->findByToken($token, false);
            // provider endpoint should be this provider endpoint, else it would be a serious integrity issue
            if ($this->meshRegistryService->getEndpoint() !== $invitation->getProviderEndpoint()) {
                throw new NotFoundException("Invitation did not came from this invitation service provider");
            }
            $data["token"] = $token;
            $data["senderName"] = $invitation->getSenderName();
            $data["remoteUserProviderName"] = $this->meshRegistryService->getName();
            $data["status"] = $invitation->getStatus();
        } catch (NotFoundException $e) {
            $this->logger->debug($e->getMessage(), ["app" => InvitationApp::APP_NAME]);
            return new DataResponse(
                [
                    'success' => false,
                    'error_message' => AppError::INVITATION_NOT_FOUND
                ],
                Http::STATUS_NOT_FOUND
            );
        } catch (ServiceException $e) {
            $this->logger->debug($e->getMessage(), ["app" => InvitationApp::APP_NAME]);
            return new DataResponse(
                [
                    "success" => false
                ],
                Http::STATUS_NOT_FOUND
            );
        } catch (Exception $e) {
            $this->logger->error($e->getMessage(), ["app" => InvitationApp::APP_NAME]);
            return new DataResponse(
                [
                    "success" => false
                ],
                Http::STATUS_NOT_FOUND
            );
        }
        return new DataResponse(
            [
                "success" => true,
                "data" => $data
            ],
            Http::STATUS_OK
        );
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     * @return bool|string
     */
    public function invitationForm()
    {
        $displayName = \OC::$server->getUserSession()->getUser()->getDisplayName();
        // verify that display name is not the user id, we prefer not to send that
        if (strtolower(\OC::$server->getUserSession()->getUser()->getUID()) === strtolower($displayName)) {
            $displayName = '';
        }
        $languageCode = \OC::$server->getL10NFactory()->findLanguage(InvitationApp::APP_NAME);
        $tmpl = new Template(InvitationApp::APP_NAME, "invitation.form", '', false, $languageCode);
        $tmpl->assign('senderName', $displayName);
        $tmpl->assign('cloudID', \OC::$server->getUserSession()->getUser()->getCloudId());
        return new DataResponse(
            [
                "success" => true,
                "data" => $tmpl->fetchPage()
            ],
            Http::STATUS_OK
        );
    }

    /**
     * Generates an invite and sends it to the specified email address.
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     * @param string $email the email address to send the invite to
     * @param string $recipientName the name of the recipient
     * @param string $senderName the name of the sender
     * @param string $message the message for the receiver
     * @return DataResponse the result
     */
    public function generateInvite(string $email = '', string $recipientName = '', string $senderName = '', string $message = ''): DataResponse
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
        if ('' == $recipientName) {
            return new DataResponse(
                [
                    'success' => false,
                    'error_message' => AppError::CREATE_INVITATION_NO_RECIPIENT_NAME,
                ],
                Http::STATUS_NOT_FOUND
            );
        }
        if ('' == $senderName) {
            return new DataResponse(
                [
                    'success' => false,
                    'error_message' => AppError::CREATE_INVITATION_NO_SENDER_NAME,
                ],
                Http::STATUS_NOT_FOUND
            );
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return new DataResponse(
                [
                    'success' => false,
                    'error_message' => AppError::CREATE_INVITATION_EMAIL_INVALID,
                ],
                Http::STATUS_NOT_FOUND
            );
        }

        // check pre-conditions
        $preConditionFailed = $this->generateInvitePreCondition();
        if ($preConditionFailed->getStatus() != Http::STATUS_OK) {
            return $preConditionFailed;
        }

        if ($email === \OC::$server->getUserSession()->getUser()->getEMailAddress()) {
            return new DataResponse(
                [
                    'success' => false,
                    'error_message' => AppError::CREATE_INVITATION_EMAIL_IS_OWN_EMAIL,
                ],
                Http::STATUS_NOT_FOUND
            );
        }

        $inviteLink = '';
        try {
            // generate the token
            $token = Uuid::uuid4();

            $params = [
                MeshRegistryService::PARAM_NAME_TOKEN => $token,
                MeshRegistryService::PARAM_NAME_PROVIDER_ENDPOINT => $this->meshRegistryService->getInvitationServiceProvider()->getEndpoint(),
            ];

            // Check for existing open and accepted invitations for the same recipient email
            // Note that accepted invitations might have another recipient's email set, so there might still already be an existing invitation
            // but this will be dealt with upon acceptance of this new invitation
            $fieldsAndValues = [];
            array_push($fieldsAndValues, [Schema::VINVITATION_REMOTE_USER_EMAIL => $email]);
            array_push($fieldsAndValues, [Schema::VINVITATION_STATUS => Invitation::STATUS_OPEN]);
            array_push($fieldsAndValues, [Schema::VINVITATION_STATUS => Invitation::STATUS_ACCEPTED]);

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

            $inviteLink = $this->meshRegistryService->inviteLink($params);
        } catch (ApplicationConfigurationException $e) {
            $this->logger->error("An error has occurred: " . $e->getMessage() . " Stacktrace: " . $e->getTraceAsString(), ['app' => InvitationApp::APP_NAME]);
            return new DataResponse(
                [
                    'success' => false,
                    'error_message' => AppError::APPLICATION_CONFIGURATION_EXCEPTION,
                ],
                Http::STATUS_NOT_FOUND,
            );
        } catch (Exception $e) {
            $this->logger->error("An error has occurred: " . $e->getMessage() . " Stacktrace: " . $e->getTraceAsString(), ['app' => InvitationApp::APP_NAME]);
            return new DataResponse(
                [
                    'success' => false,
                    'error_message' => AppError::CREATE_INVITATION_ERROR,
                ],
                Http::STATUS_NOT_FOUND,
            );
        }

        // persist the invite to send
        $invitation = new Invitation();
        $invitation->setUserCloudId(\OC::$server->getUserSession()->getUser()->getCloudId());
        $invitation->setToken($token);
        $invitation->setProviderEndpoint($this->meshRegistryService->getInvitationServiceProvider()->getEndpoint());
        $invitation->setSenderCloudId(\OC::$server->getUserSession()->getUser()->getCloudId());
        $invitation->setSenderEmail(\OC::$server->getUserSession()->getUser()->getEMailAddress());
        $invitation->setRecipientEmail($email);
        $invitation->setSenderName($senderName);
        $invitation->setTimestamp(time());
        $invitation->setStatus(Invitation::STATUS_NEW);

        // TODO: save invitation link with invitation entity and display it in the open invitations list
        //      with a re-send option perhaps?
        //      consider accepting failure of sending invitation mail, and show it as a failed invitation in the invitations list

        try {
            $mailer = \OC::$server->getMailer();
            $mail = $mailer->createMessage();
            $mail->setSubject($this->il10n->t(InvitationApp::INVITATION_EMAIL_SUBJECT));
            $mail->setFrom([$this->getEmailFromAddress('invitation-no-reply')]);
            $mail->setTo(array($email => $email));
            $language = 'en'; // actually not used, the email itself is multi language
            $htmlText = $this->getMailBody($inviteLink, $recipientName, $message, 'html', $language);
            $mail->setHtmlBody($htmlText);
            // $plainText = $this->getMailBody($inviteLink, $recipientName, $message, 'text', $language);
            // $mail->setPlainBody($plainText);
            // TODO: Array with failed recipients. Be aware that this depends on the used mail backend and therefore should be considered.
            //       return error if failed ??
            $failedRecipients = $mailer->send($mail);
            if (sizeof($failedRecipients) > 0) {
                // FIXME send back the failing recipient email (if at all possible)
                $this->logger->error(' - failed recipients: ' . print_r($failedRecipients, true), ['app' => InvitationApp::APP_NAME]);
            }
        } catch (Exception $e) {
            $this->logger->error($e->getMessage() . ' Trace: ' . $e->getTraceAsString(), ['app' => InvitationApp::APP_NAME]);
            // TODO: Instead of failing, we could continue and still insert and display the invitation as failed in the list

            // just continue for now
            // return new DataResponse(
            //     [
            //         'success' => false,
            //         'error_message' => AppError::CREATE_INVITATION_ERROR,
            //     ],
            //     Http::STATUS_NOT_FOUND
            // );
        }

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
                    'data' => [
                        'token' => $newInvitation->getToken(),
                        'inviteLink' => $inviteLink,
                        'email' => $email,
                        'recipientName' => $recipientName,
                        // FIXME: the link and message should be part of the the persisted invitation
                        'message' => "The following invite (link) has been send to $recipientName($email): <a style=\"color: blue;\" href=\"$inviteLink\">$inviteLink</a>"
                    ],
                ],
                Http::STATUS_OK
            );
        }
    }

    /**
     * Get the email from address.
     * Can be explicitly set using system config: 'invitation_mail_from_address'.
     * Otherwise uses the default config which uses the optional system config 'mail_from_address' and 'mail_domain' keys.
     *
     * @param string $address the address part in 'address@maildomain.com'
     * @return string
     */
    private function getEmailFromAddress(string $address = null)
    {
        if (empty($address)) {
            $address = 'no-reply';
        }
        $senderAddress = Util::getDefaultEmailAddress($address);
        return \OC::$server->getSystemConfig()->getValue('invitation_mail_from_address', $senderAddress);
    }

    /**
     * Returns the mail body rendered according to the specified target template.
     * @param string $inviteLink the invite link
     * @param string $recipientName the name of the recipient
     * @param string $message additional message to render
     * @param string $targetTemplate on of 'html', 'text'
     * @param string $languageCode the language code to use
     * @return string the rendered body
     */
    private function getMailBody(string $inviteLink, string $recipientName, string $message, string $targetTemplate = 'html', string $languageCode = '')
    {
        $tmpl = new Template('invitation', "mail/$targetTemplate", '', false, $languageCode);
        $tmpl->assign('recipientName', $recipientName);
        $tmpl->assign('fromName', \OC::$server->getUserSession()->getUser()->getDisplayName());
        $tmpl->assign('inviteLink', $inviteLink);
        $tmpl->assign('message', $message);
        return $tmpl->fetchPage();
    }

    /**
     * Handle the invite by creating the notification with the option to accept or reject it.
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     * @param string $token the token
     * @param string $senderEndpoint the endpoint of the sender
     * @param string $senderEmail the email of the sender
     * @return RedirectResponse
     */
    public function handleInvite(string $token = '', string $providerEndpoint = ''): RedirectResponse
    {
        $urlGenerator = \OC::$server->getURLGenerator();

        if ($token == '') {
            \OC::$server->getLogger()->error('Invite is missing the token.', ['app' => InvitationApp::APP_NAME]);
            return new RedirectResponse($urlGenerator->linkToRoute(InvitationApp::APP_NAME . '.error.invitation', ['message' => AppError::HANDLE_INVITATION_ERROR]));
        }
        if ($providerEndpoint == '') {
            \OC::$server->getLogger()->error('Invite is missing the invitation service provider endpoint.', ['app' => InvitationApp::APP_NAME]);
            return new RedirectResponse($urlGenerator->linkToRoute(InvitationApp::APP_NAME . '.error.invitation', ['message' => AppError::HANDLE_INVITATION_ERROR]));
        }

        if (!$this->meshRegistryService->isKnowInvitationServiceProvider($providerEndpoint)) {
            \OC::$server->getLogger()->error('Invitation service provider endpoint is unknown.', ['app' => InvitationApp::APP_NAME]);
            return new RedirectResponse($urlGenerator->linkToRoute(InvitationApp::APP_NAME . '.error.invitation', ['message' => AppError::HANDLE_INVITATION_ERROR]));
        }

        // check if invitation doesn't already exists
        try {
            // we want a NotFoundException
            $invitation = $this->invitationService->findByToken($token);

            if ($invitation->getStatus() === Invitation::STATUS_OPEN) {
                // redirect to the open invitation
                return new RedirectResponse($urlGenerator->linkToRoute('invitation.invitation.index'));
            }
            if ($invitation->getStatus() === Invitation::STATUS_ACCEPTED) {
                return new RedirectResponse($urlGenerator->linkToRoute(InvitationApp::APP_NAME . '.error.invitation', ['message' => AppError::HANDLE_INVITATION_ALREADY_ACCEPTED, 'param1' => $invitation->getSenderName()]));
            }
            return new RedirectResponse($urlGenerator->linkToRoute(InvitationApp::APP_NAME . '.error.invitation', ['message' => AppError::HANDLE_INVITATION_EXISTS, 'param1' => $this->il10n->t($invitation->getStatus())]));
        } catch (NotFoundException $e) {
            // we're good to go
        } catch (Exception $e) {
            $this->logger->error($e->getMessage(), ['app' => InvitationApp::APP_NAME]);
            return new RedirectResponse($urlGenerator->linkToRoute(InvitationApp::APP_NAME . '.error.invitation', ['message' => AppError::HANDLE_INVITATION_ERROR]));
        }

        //retrieve the sender info
        $url = $providerEndpoint . MeshRegistryService::ENDPOINT_GET_INVITE . "/$token";
        $httpClient = new HttpClient();
        $response = $httpClient->curlGet($url);
        if ($response['success'] == false) {
            $this->logger->error("Failed to retrieve the invitation with token $token from $providerEndpoint: " . print_r($response, true), ['app' => InvitationApp::APP_NAME]);
            $this->logger->error($e->getTraceAsString(), ['app' => InvitationApp::APP_NAME]);
            return new RedirectResponse(
                $urlGenerator->linkToRoute(
                    InvitationApp::APP_NAME . '.error.invitation',
                    [
                        'message' => AppError::GET_INVITE_ERROR
                    ]
                )
            );
        }
        $data = (array)$response['data'];
        if (
            $data['status'] !== Invitation::STATUS_OPEN
            || $data['token'] !== $token
        ) {
            $this->logger->error("Unable to handle invitation. /invite response from sender seems invalid. Response: " . print_r($response, true), ['app' => InvitationApp::APP_NAME]);
            return new RedirectResponse(
                $urlGenerator->linkToRoute(
                    InvitationApp::APP_NAME . '.error.invitation',
                    [
                        'message' => AppError::HANDLE_INVITATION_INVALID_INVITELINK
                    ]
                )
            );
        }

        $senderName = $data['senderName'];

        // persist the received invite
        $invitation = new Invitation();
        $invitation->setUserCloudId(\OC::$server->getUserSession()->getUser()->getCloudId());
        $invitation->setToken($token);
        $invitation->setProviderEndpoint($providerEndpoint);
        $invitation->setSenderName($senderName);
        $invitation->setRecipientEndpoint($this->meshRegistryService->getInvitationServiceProvider()->getEndpoint());
        $invitation->setRecipientCloudId(\OC::$server->getUserSession()->getUser()->getCloudId());
        $invitation->setRecipientName(\OC::$server->getUserSession()->getUser()->getDisplayName());
        $invitation->setRecipientEmail(\OC::$server->getUserSession()->getUser()->getEMailAddress());
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

        $notification->setApp(InvitationApp::APP_NAME)
            // the user that has received the invite is logged in at this point
            ->setUser(\OC::$server->getUserSession()->getUser()->getUID())
            ->setDateTime(new DateTime())
            ->setObject(MeshRegistryService::PARAM_NAME_TOKEN, $token)
            ->setSubject('invitation', [
                MeshRegistryService::PARAM_NAME_TOKEN => $token,
                MeshRegistryService::PARAM_NAME_PROVIDER_ENDPOINT => $providerEndpoint,
                MeshRegistryService::PARAM_NAME_NAME => $senderName,
            ])
            ->setLink($urlGenerator->linkToRoute('invitation.invitation.index'));

        $manager->notify($notification);

        return new RedirectResponse($urlGenerator->linkToRoute('invitation.invitation.index'));
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
        try {
            if ($token == '') {
                $this->logger->error('acceptInvite: missing parameter token.', ['app' => InvitationApp::APP_NAME]);
                return new DataResponse(
                    ['success' => false, 'error_message' => AppError::REQUEST_MISSING_PARAMETER],
                    Http::STATUS_NOT_FOUND
                );
            }

            $invitation = null;
            try {
                $invitation = $this->invitationService->findByToken($token);
            } catch (NotFoundException $e) {
                $this->logger->error("acceptInvite: invitation not found for token '$token'", ['app' => InvitationApp::APP_NAME]);
                return new DataResponse(
                    ['success' => false, 'error_message' => AppError::INVITATION_NOT_FOUND],
                    Http::STATUS_NOT_FOUND
                );
            }

            // check pre-conditions
            $preConditionFailed = $this->acceptInvitePreCondition();
            if ($preConditionFailed->getStatus() != Http::STATUS_OK) {
                return $preConditionFailed;
            }

            $recipientEndpoint = $this->meshRegistryService->getEndpoint();
            $recipientCloudID = \OC::$server->getUserSession()->getUser()->getCloudId();
            $recipientEmail = \OC::$server->getUserSession()->getUser()->getEMailAddress();
            $recipientName = \OC::$server->getUserSession()->getUser()->getDisplayName();
            $params = [
                MeshRegistryService::PARAM_NAME_RECIPIENT_PROVIDER => $recipientEndpoint,
                MeshRegistryService::PARAM_NAME_TOKEN => $token,
                MeshRegistryService::PARAM_NAME_USER_ID => $recipientCloudID,
                MeshRegistryService::PARAM_NAME_EMAIL => $recipientEmail,
                MeshRegistryService::PARAM_NAME_NAME => $recipientName,
            ];

            $url = $this->meshRegistryService->getFullInviteAcceptedEndpointURL($invitation->getProviderEndpoint());
            $httpClient = new HttpClient();
            $response = $httpClient->curlPost($url, $params);

            if (isset($response['success']) && $response['success'] == false) {
                $this->logger->error('Failed to accept the invitation: /invite-accepted failed with response: ' . print_r($response, true), ['app' => InvitationApp::APP_NAME]);
                return new DataResponse(
                    [
                        'success' => false,
                        'error_message' => (isset($response['error_message']) ? $response['error_message'] : AppError::HANDLE_INVITATION_ERROR)
                    ],
                    Http::STATUS_NOT_FOUND
                );
            }
            // note: beware of the format of response of the OCM call, it has no 'data' field
            if ($this->verifiedInviteAcceptedResponse($response) == false) {
                $this->logger->error('Failed to accept the invitation - returned fields not valid: ' . print_r($response, true), ['app' => InvitationApp::APP_NAME]);
                return new DataResponse(
                    [
                        'success' => false,
                        'error_message' => AppError::HANDLE_INVITATION_OCM_INVITE_ACCEPTED_RESPONSE_FIELDS_INVALID
                    ],
                    Http::STATUS_NOT_FOUND
                );
            }

            // withdraw any previous accepted invitation from the same inviter
            $existingInvitationsReceived = $this->invitationService->findAll([
                [Schema::VINVITATION_SENDER_CLOUD_ID => $response[MeshRegistryService::PARAM_NAME_USER_ID]],
                [Schema::VINVITATION_RECIPIENT_CLOUD_ID => $recipientCloudID],
                [Schema::VINVITATION_STATUS => Invitation::STATUS_ACCEPTED],
            ]);
            $existingInvitationsSent = $this->invitationService->findAll([
                [Schema::VINVITATION_RECIPIENT_CLOUD_ID => $response[MeshRegistryService::PARAM_NAME_USER_ID]],
                [Schema::VINVITATION_SENDER_CLOUD_ID => $recipientCloudID],
                [Schema::VINVITATION_STATUS => Invitation::STATUS_ACCEPTED],
            ]);
            $existingInvitations = array_merge($existingInvitationsReceived, $existingInvitationsSent);
            if (count($existingInvitations) > 0) {
                foreach ($existingInvitations as $existingInvitation) {
                    $this->logger->debug("A previous invitation for remote user with name " . $response[MeshRegistryService::PARAM_NAME_NAME] . " was accepted already. Withdrawing that one", ['app' => InvitationApp::APP_NAME]);
                    $updateResult = $this->invitationService->update([
                        Schema::INVITATION_TOKEN => $existingInvitation->getToken(),
                        Schema::INVITATION_STATUS => Invitation::STATUS_WITHDRAWN,
                    ]);
                    if ($updateResult == false) {
                        return new DataResponse(
                            [
                                'success' => false,
                                'error_message' => AppError::ACCEPT_INVITE_ERROR,
                            ],
                            Http::STATUS_NOT_FOUND,
                        );
                    }
                }
            }

            // all's well, update the open invitation
            $updateResult = $this->invitationService->update(
                [
                    Schema::INVITATION_TOKEN => $invitation->getToken(),
                    Schema::INVITATION_SENDER_CLOUD_ID => $response[MeshRegistryService::PARAM_NAME_USER_ID],
                    Schema::INVITATION_SENDER_EMAIL => $response[MeshRegistryService::PARAM_NAME_EMAIL],
                    Schema::INVITATION_SENDER_NAME => $response[MeshRegistryService::PARAM_NAME_NAME],
                    Schema::INVITATION_STATUS => Invitation::STATUS_ACCEPTED,
                ],
                true
            );
            if ($updateResult == false) {
                $this->logger->error("Failed to handle /accept-invite (invitation with token '$token' could not be updated).", ['app' => InvitationApp::APP_NAME]);
                return new DataResponse(
                    [
                        'success' => false,
                        'error_message' => AppError::ACCEPT_INVITE_ERROR,
                    ],
                    Http::STATUS_NOT_FOUND
                );
            }

            $this->removeInvitationNotification($token);

            return new DataResponse(
                [
                    'success' => true
                ],
                Http::STATUS_OK
            );
        } catch (Exception $e) {
            $this->logger->error($e->getMessage() . ' Trace: ' . $e->getTraceAsString(), ['app]' => InvitationApp::APP_NAME]);
            return new DataResponse(
                [
                    'success' => false,
                    'error_message' => AppError::ACCEPT_INVITE_ERROR,
                ],
                Http::STATUS_NOT_FOUND,
            );
        }
    }

    /**
     * Returns a DataResponse with an error why the precondition failed,
     * or null when it hasn't.
     */
    private function generateInvitePreCondition(): DataResponse
    {
        $_userEmail = \OC::$server->getUserSession()->getUser()->getEMailAddress();
        if (!isset($_userEmail) || $_userEmail === '') {
            return new DataResponse(
                [
                    'success' => false,
                    'error_message' => AppError::CREATE_INVITATION_ERROR_SENDER_EMAIL_MISSING,
                ],
                Http::STATUS_NOT_FOUND,
            );
        }
        return new DataResponse(
            [
                'success' => true,
            ],
            Http::STATUS_OK,
        );
    }

    /**
     * Returns a DataResponse with an error why the precondition failed,
     * or null when it hasn't.
     */
    private function acceptInvitePreCondition(): DataResponse
    {
        $_userEmail = \OC::$server->getUserSession()->getUser()->getEMailAddress();
        if (!isset($_userEmail) || $_userEmail === '') {
            return new DataResponse(
                [
                    'success' => false,
                    'error_message' => AppError::ACCEPT_INVITE_ERROR_RECIPIENT_EMAIL_MISSING,
                ],
                Http::STATUS_NOT_FOUND,
            );
        }
        $_userName = \OC::$server->getUserSession()->getUser()->getDisplayName();
        if (!isset($_userName) || $_userName === '') {
            return new DataResponse(
                [
                    'success' => false,
                    'error_message' => AppError::ACCEPT_INVITE_ERROR_RECIPIENT_NAME_MISSING,
                ],
                Http::STATUS_NOT_FOUND,
            );
        }
        return new DataResponse(
            [
                'success' => true,
            ],
            Http::STATUS_OK,
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
            $updateResult = $this->invitationService->update([
                Schema::INVITATION_TOKEN => $token,
                Schema::INVITATION_STATUS => Invitation::STATUS_DECLINED,
            ]);

            if ($updateResult == true) {
                // remove notification
                $this->removeInvitationNotification($token);

                return new DataResponse(
                    [
                        'success' => true,
                        // TODO consider returning the updated invitation
                    ],
                    Http::STATUS_OK,
                );
            }
            return new DataResponse(
                [
                    'success' => false,
                    'error_message' => AppError::DECLINE_INVITE_ERROR,
                ],
                Http::STATUS_NOT_FOUND,
            );
        } catch (NotFoundException $e) {
            $this->logger->error("declineInvite: invitation not found for token '$token'", ['app' => InvitationApp::APP_NAME]);
            return new DataResponse(
                [
                    'success' => false,
                    'error_message' => AppError::INVITATION_NOT_FOUND,
                ],
                Http::STATUS_NOT_FOUND,
            );
        } catch (Exception $e) {
            $this->logger->error($e->getMessage() . ' Trace: ' . $e->getTraceAsString(), ['app]' => InvitationApp::APP_NAME]);
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
     * Removes the notification that is associated with the invitation with specified token.
     *
     * @param string $token
     * @return void
     */
    private function removeInvitationNotification(string $token): void
    {
        $this->logger->debug(" - removing notification for invitation with token '$token'");
        try {
            $manager = \OC::$server->getNotificationManager();
            $notification = $manager->createNotification();
            $notification
                ->setApp(InvitationApp::APP_NAME)
                ->setUser(\OC::$server->getUserSession()->getUser()->getUID())
                ->setObject(MeshRegistryService::PARAM_NAME_TOKEN, $token);
            $manager->markProcessed($notification);
        } catch (Exception $e) {
            $this->logger->error('Remove notification failed: ' . $e->getMessage() . ' Trace: ' . $e->getTraceAsString(), ['app' => InvitationApp::APP_NAME]);
            throw $e;
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
            $this->logger->error("find() - missing parameter 'id'.", ['app' => InvitationApp::APP_NAME]);
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
                    'data' => $invitation->jsonSerialize(),
                ]
            );
        } catch (NotFoundException $e) {
            $this->logger->error("invitation not found for id $id. Error: " . $e->getMessage() . ' Trace: ' . $e->getTraceAsString(), ['app' => InvitationApp::APP_NAME]);
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
            $this->logger->error("findAll() - missing parameter 'fields'.", ['app' => InvitationApp::APP_NAME]);
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
                    'data' => $invitations,
                ],
                Http::STATUS_OK
            );
        } catch (Exception $e) {
            $this->logger->error('invitations not found for fields: ' . print_r($fields, true) . 'Error: ' . $e->getMessage() . ' Trace: ' . $e->getTraceAsString(), ['app' => InvitationApp::APP_NAME]);
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
            $this->logger->error("findByToken() - missing parameter 'token'.", ['app' => InvitationApp::APP_NAME]);
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
                    'data' => $invitation->jsonSerialize(),
                ],
                Http::STATUS_OK,
            );
        } catch (NotFoundException $e) {
            $this->logger->error("invitation not found for token '$token'. Error: " . $e->getMessage() . ' Trace: ' . $e->getTraceAsString(), ['app' => InvitationApp::APP_NAME]);
            return new DataResponse(
                [
                    'success' => false,
                    'error_message' => AppError::INVITATION_NOT_FOUND,
                ],
                Http::STATUS_NOT_FOUND,
            );
        } catch (Exception $e) {
            $this->logger->error("invitation not found for token '$token'. Error: " . $e->getMessage() . ' Trace: ' . $e->getTraceAsString(), ['app' => InvitationApp::APP_NAME]);
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
     * Update the invitation. Only the status can be updated.
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @param string $token the token of the invitation
     * @param string $status the new status
     * @return DataResponse
     */
    public function update(string $token, string $status): DataResponse
    {
        if (!isset($token) && !isset($status)) {
            return new DataResponse(
                [
                    'success' => false,
                    'error_message' => AppError::UPDATE_INVITATION_ERROR
                ],
                Http::STATUS_NOT_FOUND,
            );
        }

        $result = $this->invitationService->update([
            Schema::INVITATION_TOKEN => $token,
            Schema::INVITATION_STATUS => $status,
        ]);

        if (
            $status === Invitation::STATUS_DECLINED
            || $status === Invitation::STATUS_REVOKED
        ) {
            // remove potential associated notification
            $this->removeInvitationNotification($token);
        }

        if ($result === true) {
            return new DataResponse(
                [
                    'success' => true,
                    'data' => $result,
                ],
                Http::STATUS_OK,
            );
        }
        return new DataResponse(
            [
                'success' => false,
                'error_message' => AppError::UPDATE_INVITATION_ERROR
            ],
            Http::STATUS_NOT_FOUND,
        );
    }
}
