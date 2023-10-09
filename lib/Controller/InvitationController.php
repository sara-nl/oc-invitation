<?php

/**
 * 
 */

namespace OCA\RDMesh\Controller;

use DateTime;
use OC;
use OCA\RDMesh\HttpClient;
use OCA\RDMesh\Service\MeshService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Http\RedirectResponse;
use OCP\IRequest;
use Ramsey\Uuid\Uuid;

class InvitationController extends Controller
{

    private MeshService $meshService;

    public function __construct(
        $appName,
        IRequest $request,
        MeshService $meshService
    ) {
        parent::__construct($appName, $request);
        $this->meshService = $meshService;
    }

    /**
     * Generates an invite and sends it to the specified email address.

     * @NoAdminRequired
     * @NoCSRFRequired
     * @param string email the email address to send the invite to
     * @return DataResponse the result
     */
    public function generateInvite(string $email = ''): DataResponse
    {
        if ('' == $email) {
            return new DataResponse(
                ['message' => 'You must provide the email address of the intended receiver of the invite.'],
                Http::STATUS_NOT_FOUND
            );
        }

        // generate the token
        // TODO: persist this token
        $token = Uuid::uuid4();

        // add the necessary parameters to the link
        $params = [
            MeshService::PARAM_NAME_TOKEN => $token,
            MeshService::PARAM_NAME_SENDER_DOMAIN => $this->meshService->getDomain(),
            MeshService::PARAM_NAME_SENDER_EMAIL => \OC::$server->getUserSession()->getUser()->getEmailAddress(),
        ];

        $inviteLink = $this->meshService->inviteLink($params);

        // TODO: send an email with the invitation link to the receiver ($email)

        return new DataResponse(
            [
                'message' => 'This invite will be send to ' . $email,
                'inviteLink' => $inviteLink,
            ],
            Http::STATUS_OK
        );
    }

    /**
     * Handle the invite by giving the option to accept or reject it.
     * 
     * @NoAdminRequired
     * @NoCSRFRequired
     * @param string $token the token
     * @param string $senderDomain the domain of the sender
     * @param string $senderEmail the email of the sender
     * @return RedirectResponse
     */
    public function handleInvite(string $token = '', string $senderDomain = '', string $senderEmail = ''): RedirectResponse
    {

        // TODO: do checks: token, sender domain, sender email, ...
        // TODO: persist invite

        $manager = \OC::$server->getNotificationManager();
        $notification = $manager->createNotification();

        $acceptAction = $notification->createAction();
        $acceptAction
            ->setLabel('accept')
            ->setLink("/apps/rd-mesh/accept-invite?token=$token", 'GET');

        $declineAction = $notification->createAction();
        $declineAction->setLabel('decline')
            ->setLink('/apps/rd-mesh/decline-invite', 'DELETE');

        $notification->setApp('notification-invite')
            /* the user that has received the invite is logged in at this point */
            ->setUser(OC::$server->getUserSession()->getUser()->getUID())
            ->setDateTime(new DateTime())
            // FIXME: find out on what object actually means
            ->setObject('senderDomain', $senderDomain)
            ->setSubject('invitation', [
                MeshService::PARAM_NAME_TOKEN => $token,
                MeshService::PARAM_NAME_SENDER_DOMAIN => $senderDomain,
                MeshService::PARAM_NAME_SENDER_EMAIL => $senderEmail
            ])
            ->addAction($acceptAction)
            ->addAction($declineAction);

        $manager->notify($notification);

        $urlGenerator = \OC::$server->getURLGenerator();
        return new RedirectResponse($urlGenerator->linkToRoute('files.view.index'));
    }

    /**
     * Notify the inviter that we accept the invite.
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
        \OC::$server->getLogger()->debug(" - acceptInvite - " . $token);

        if ($token == '') {
            return new DataResponse(
                ['error' => 'sender token missing'],
                Http::STATUS_NOT_FOUND
            );
        }

        // TODO: retrieve invite from db
        $dummySenderDomain = "rd-1.nl";

        $params = [
            MeshService::PARAM_NAME_RECIPIENT_PROVIDER => $this->meshService->getDomain(),
            MeshService::PARAM_NAME_TOKEN => $token,
            MeshService::PARAM_NAME_USER_ID => \OC::$server->getUserSession()->getUser()->getCloudId(),
            MeshService::PARAM_NAME_EMAIL => \OC::$server->getUserSession()->getUser()->getEMailAddress(),
            MeshService::PARAM_NAME_NAME => \OC::$server->getUserSession()->getUser()->getDisplayName()
        ];
        $url = $this->meshService->getFullInviteAcceptedEndpointURL($dummySenderDomain);
        $httpClient = new HttpClient();
        $response = $httpClient->curlPost($url, $params);
        if ($response['success'] == true) {
            return new DataResponse(
                $response,
                Http::STATUS_OK
            );
        }
        return new DataResponse(
            $response,
            Http::STATUS_NOT_FOUND
        );
    }
}
