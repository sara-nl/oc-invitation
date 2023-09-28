<?php

/**
 * 
 */

namespace OCA\RDMesh\Controller;

use DateTime;
use OCA\RDMesh\Service\RDMeshService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\IRequest;
use OCP\IUserSession;

class InvitationController extends Controller
{

    private RDMeshService $rdMeshService;
    private IUserSession $userSession;
    private ITimeFactory $timeFactory;
    private MeshRegistryController $meshRegistry;

    public function __construct(
        $appName,
        IRequest $request,
        IUserSession $userSession,
        ITimeFactory $timeFactory,
        RDMeshService $rdMeshService,
        MeshRegistryController $meshRegistry
    ) {
        parent::__construct($appName, $request);
        $this->userSession = $userSession;
        $this->timeFactory = $timeFactory;
        $this->rdMeshService = $rdMeshService;
        $this->meshRegistry = $meshRegistry;
    }

    /**
     * Generates an invite
     * 
     * @NoCSRFRequired
     */
    public function generateInvite(string $email = '')
    {
        \OC::$server->getLogger()->debug('generateInvite from email ' . $this->request->getParam(RDMeshService::PARAM_NAME_EMAIL));
        if ('' == $email) {
            return new DataResponse(
                ['message' => 'You must provide the email address of the intended receiver of the invite.'],
                Http::STATUS_NOT_FOUND
            );
        }


        /* TODO send an email with the invitation link to the receiver */
        $inviteLink = $this->rdMeshService->inviteLink();

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
     * @NoCSRFRequired
     */
    public function handleInvite(string $token = '', string $senderDomain = '')
    {
        \OC::$server->getLogger()->debug(" handleInvite : $token, $senderDomain");

        /* @TODO do checks: token, sender domain, ... */

        $manager = \OC::$server->getNotificationManager();
        $notification = $manager->createNotification();
        // $notification->setMessage("$tokenValue from $senderDomainValue wants to share data with you, please click accept to accept the invitation. Or reject to decline.");

        $acceptAction = $notification->createAction();
        $acceptInviteEndpointURL = $this->rdMeshService->getFullAcceptInviteEndpointURL();
        $acceptAction
            ->setLabel('accept')
            ->setLink($acceptInviteEndpointURL, 'POST');
        $rejectAction = $notification->createAction();
        $rejectAction
            ->setLabel('reject')
            ->setLink('reject', 'DELETE');

        $user = $this->userSession->getUser();
        $time = $this->timeFactory->getTime();
        $datetime = new DateTime();
        $datetime->setTimestamp($time);
        $notification
            ->setApp($this->appName)
            ->setUser($user->getUID())
            ->setDateTime($datetime)
            ->setObject($this->appName, dechex($time))
            ->setSubject('invitation', ['parameters'])
            ->addAction($acceptAction)
            ->addAction($rejectAction);

        $manager->notify($notification);

        /* @FIXME when the notification works remove this redirect and handle the notification action accept/reject links */

        return $this->acceptInvite($token, $senderDomain);
    }

    /**
     * Save the invite and respond to the inviter through an /invite-accepted POST.
     * 
     * @return DataResponse
     */
    public function acceptInvite(string $token = '', string $senderDomain = '')
    {
        /* FIXME Build a POST containing sender and receiver token */

        $tokenParam = RDMeshService::PARAM_NAME_TOKEN;
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
        $fullInviteAcceptedEndpointURL = $this->rdMeshService->getFullInviteAcceptedEndpointURL($senderDomain);

        /* TODO persist the invitation (sender token, domain) */

        $recipientTokenParam = RDMeshService::PARAM_NAME_RECIPIENT_TOKEN;
        $recipientTokenValue = \OC::$server->getUserSession()->getUser()->getCloudId();

        $acceptInviteURL = "$fullInviteAcceptedEndpointURL?$tokenParam=$token&$recipientTokenParam=$recipientTokenValue";

        return new DataResponse(
            ['message' => "Follow the accept invite URL to accept the invite from $token", 'acceptInviteURL' => $acceptInviteURL],
            Http::STATUS_OK
        );
    }
}
