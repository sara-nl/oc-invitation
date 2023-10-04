<?php

/**
 * 
 */

namespace OCA\RDMesh\Controller;

use DateTime;
use OC;
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
     * Generates an invite

     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function generateInvite(string $email = '')
    {
        if ('' == $email) {
            return new DataResponse(
                ['message' => 'You must provide the senderEmail address of the intended receiver of the invite.'],
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
     */
    public function handleInvite(string $token = '', string $senderDomain = '', string $senderEmail = '')
    {

        /* TODO: do checks: token, sender domain, sender email, ... */

        $manager = \OC::$server->getNotificationManager();
        $notification = $manager->createNotification();

        $acceptAction = $notification->createAction();
        $acceptAction
            ->setLabel('accept')
            ->setLink('/apps/rd-mesh/invitation/accept-invite', 'POST');

        $declineAction = $notification->createAction();
        $declineAction->setLabel('decline')
            ->setLink('/apps/rd-mesh/invitation/decline-invite', 'DELETE');

        $notification->setApp('notification-invite')
            /* the user that has received the invite is logged in at this point */
            ->setUser(OC::$server->getUserSession()->getUser()->getUID())
            ->setDateTime(new DateTime())
            // FIXME: find out on what object actually means
            ->setObject('senderDomain', $senderDomain)
            ->setSubject('invitation', [
                MeshService::PARAM_NAME_TOKEN => $token, 
                MeshService::PARAM_NAME_SENDER_DOMAIN => $senderDomain,
                MeshService::PARAM_NAME_SENDER_EMAIL => $senderEmail])
            ->addAction($acceptAction)
            ->addAction($declineAction);

        $manager->notify($notification);

        $urlGenerator = \OC::$server->getURLGenerator();
        return new RedirectResponse($urlGenerator->linkToRoute('files.view.index'));
    }

    /**
     * Save the invite and respond to the inviter through an /invite-accepted POST.
     * 
     * @NoAdminRequired
     * @return DataResponse
     */
    public function acceptInvite(string $token = '', string $senderDomain = '')
    {
        /* FIXME: Build a POST containing sender and receiver token */

        $tokenParam = MeshService::PARAM_NAME_TOKEN;
        if ($token == '') {
            return new DataResponse(
                ['error' => 'sender token missing'],
                Http::STATUS_NOT_FOUND
            );
        }

        if ($senderDomain == '') {
            return new DataResponse(
                ['error' => 'sender domain missing'],
                Http::STATUS_NOT_FOUND
            );
        }
        $fullInviteAcceptedEndpointURL = $this->meshService->getFullInviteAcceptedEndpointURL($senderDomain);

        /* TODO: persist the invitation (sender token, domain) */

        $recipientTokenParam = MeshService::PARAM_NAME_RECIPIENT_TOKEN;
        $recipientTokenValue = \OC::$server->getUserSession()->getUser()->getCloudId();

        $acceptInviteURL = "$fullInviteAcceptedEndpointURL?$tokenParam=$token&$recipientTokenParam=$recipientTokenValue";

        return new DataResponse(
            ['message' => "Follow the accept invite URL to accept the invite from $token", 'acceptInviteURL' => $acceptInviteURL],
            Http::STATUS_OK
        );
    }
}
