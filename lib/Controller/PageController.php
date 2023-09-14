<?php
/**
 * 
 */

namespace OCA\RDMesh\Controller;

use OCP\AppFramework\Controller;

class PageController extends Controller {

    /**
	 * @NoCSRFRequired
     */
    public function generateInvite() {
        \OC::$server->getLogger()->debug(' generateInvite');
        return ['test' => 'An invite will be generated.'];
    }
}