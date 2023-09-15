<?php
/**
 *
 */

namespace OCA\RDMesh\Controller;

use OCP\AppFramework\Controller;

class MeshRegistryController extends Controller {

    /**
     * Returns the domain of this mesh node.
     * @NoCSRFRequired
     */
    public function getDomain() {
        // TODO: should return the configurable mesh domain name of this mesh node.
        return ['domain' => \OC::$server->getRequest()->getServerHost()];
    }
}