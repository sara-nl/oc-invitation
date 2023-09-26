<?php
/**
 * 
 */

namespace OCA\RDMesh\Controller;

use DateTime;
use OCA\RDMesh\Service\RDMeshService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\IRequest;
use OCP\IUserSession;

class InvitationController extends Controller {

    private RDMeshService $rdMeshService;
    private IUserSession $userSession;
    private ITimeFactory $timeFactory;

    public function __construct(
            $appName, 
            IRequest $request, 
            IUserSession $userSession, 
            ITimeFactory $timeFactory,
            RDMeshService $rdMeshService) {
        parent::__construct($appName, $request);
        $this->userSession = $userSession;
        $this->timeFactory = $timeFactory;
        $this->rdMeshService = $rdMeshService;
    }

    /**
	 * @NoCSRFRequired
     */
    public function generateInvite($email) {
        if ("" == $email) {
            return ['message' => 'You must provide the email address of the intended receiver of the invite.'];
        }

        // the forward invite endpoint
        $forwardInviteEndpoint = $this->rdMeshService->getFullForwardInviteEndpoint();

        // request the domain from the mesh registry service
        $domainKey = RDMeshService::PARAM_NAME_SENDER_DOMAIN;
        $domainValue = $this->rdMeshService->getDomain();

        // the token is the federated ID of the session user
        $tokenKey = RDMeshService::PARAM_NAME_TOKEN;
        $tokenValue = \OC::$server->getUserSession()->getUser()->getCloudId();

        $invitationLink = "$forwardInviteEndpoint?$domainKey=$domainValue&$tokenKey=$tokenValue";

        /* TODO send an email with the invitation link to the receiver */

        return [
            'message' => 'This invite will be send to ' . $email,
            'inviteLink' => $invitationLink,
        ];
    }

    /**
     * Handle the invite by giving the option to accept or reject it.
     * @NoCSRFRequired
     */
    public function handleInvite() {
        \OC::$server->getLogger()->debug(' --- handling invite ---');

        $token = RDMeshService::PARAM_NAME_TOKEN;
        $tokenValue = $this->request->getParam(RDMeshService::PARAM_NAME_TOKEN);
        $senderDomain = RDMeshService::PARAM_NAME_SENDER_DOMAIN;
        $senderDomainValue = $this->request->getParam(RDMeshService::PARAM_NAME_SENDER_DOMAIN);

        /* @TODO do checks: sender domain, ... */

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

        return $this->acceptInvite();
    }

    /**
     * Save the invite and respond to the inviter through an /invite-accepted POST.
     * 
     */
    public function acceptInvite() {
        /* FIXME Build a POST containing sender and receiver token */

        $senderDomain = $this->request->getParam(RDMeshService::PARAM_NAME_SENDER_DOMAIN, "");
        if($senderDomain == "") {
            return ['error' => 'sender domain missing'];
        }
        $fullInviteAcceptedEndpointURL = $this->rdMeshService->getFullInviteAcceptedEndpointURL($senderDomain);
        $token = RDMeshService::PARAM_NAME_TOKEN;
        $tokenValue = $this->request->getParam(RDMeshService::PARAM_NAME_TOKEN, "");
        if($tokenValue == "") {
            return ['error' => 'sender token missing'];
        }

        /* TODO persist the invitation (sender token, domain) */

        $recipientToken = RDMeshService::PARAM_NAME_RECIPIENT_TOKEN;
        $recipientTokenValue = \OC::$server->getUserSession()->getUser()->getCloudId();
        $acceptInviteURL = "$fullInviteAcceptedEndpointURL?$token=$tokenValue&$recipientToken=$recipientTokenValue";
       
        return ['message' =>"Follow the accept invite URL to accept the invite from $tokenValue", 'inviteAcceptedURL' => $acceptInviteURL];
    }
}