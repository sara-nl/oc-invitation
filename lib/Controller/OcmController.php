<?php
/**
 * OCM controller
 */

namespace OCA\RDMesh\Controller;

use OCA\RDMesh\Service\RDMeshService;
use OCP\AppFramework\Controller;
use OCP\IRequest;

class OcmController extends Controller {

    public function __construct($appName, IRequest $request) {
        parent::__construct($appName, $request);
    }

    /**
     * Inform the sender of a share that it has been accepted by the receiver.
     * 
     * @NoCSRFRequired
     * @PublicPage
     */
    public function inviteAccepted() {
        $token = $this->request->getParam(RDMeshService::PARAM_NAME_TOKEN, "");
        if ($token == "") {
            return ['error' => 'sender token missing'];
        }
        $recipientToken = $this->request->getParam(RDMeshService::PARAM_NAME_RECIPIENT_TOKEN, "");
        if ($recipientToken == "") {
            return ['error' => 'recipient token missing'];
        }
        /** 
         * TODO At this point we should persist the recipient token and we can start sharing.
         * TODO the recipient already has the sender's token, consider '/invite-accepted' to be required to accept shares from the recipient.
         */

        return ['message' => "You have accepted the invitation from $token. Your token ($recipientToken) has been send to $token. You can now begin sharing content with each other."];
    }
}