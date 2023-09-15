<?php
/**
 * 
 */

namespace OCA\RDMesh\Controller;

use OCP\AppFramework\Controller;

class InvitationController extends Controller {

    /**
	 * @NoCSRFRequired
     */
    public function generateInvite($email) {
        if ("" == $email) {
            return ['message' => 'You must provide an email address of the intended receiver of the invite.'];
        }

        
        return [
            'message' => 'An invite will be generated and send to ' . $email . '.',
            'inviteLink' => 'The invitation link.'
        ];
    }
}