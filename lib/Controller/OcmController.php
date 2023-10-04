<?php

/**
 * OCM controller
 */

namespace OCA\RDMesh\Controller;

use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;

class OcmController extends Controller
{

    public function __construct($appName, IRequest $request)
    {
        parent::__construct($appName, $request);
    }

    /**
     * Inform the sender of a share that it has been accepted by the receiver.
     * 
     * FIXME: use method parameters
     * 
     * @NoCSRFRequired
     * @PublicPage
     */
    public function inviteAccepted(string $token = '', string $recipientToken = '')
    {
        if ($token == '') {
            return new DataResponse(
                ['error' => 'sender token missing'],
                Http::STATUS_NOT_FOUND
            );
        }
        if ($recipientToken == '') {
            return new DataResponse(
                ['error' => 'recipient token missing'], 
                Http::STATUS_NOT_FOUND
            );
        }
        /** 
         * TODO At this point we should persist the recipient token and we can start sharing.
         * TODO the recipient already has the sender's token, consider '/invite-accepted' to be required to accept shares from the recipient.
         */

        return new DataResponse(
            ['message' => "You have accepted the invitation from $token. Your token ($recipientToken) has been send to $token. You can now begin sharing content with each other."],
            Http::STATUS_OK
        );
    }
}
