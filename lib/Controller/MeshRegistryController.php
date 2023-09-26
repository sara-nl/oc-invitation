<?php
/**
 * This is the mesh registry controller.
 * Endpoints:
 *      /get-domain
 *      /forward-invite
 */

namespace OCA\RDMesh\Controller;

use OCA\RDMesh\Service\RDMeshService;
use OCA\Federation\TrustedServers;
use OCP\AppFramework\Controller;
use OCP\IRequest;

class MeshRegistryController extends Controller {

    private TrustedServers $trustedServers;


    public function __construct($appName, IRequest $request, TrustedServers $trustedServers) { 
            parent::__construct($appName, $request);
            $this->trustedServers = $trustedServers;
    }

    private function getWAYF() {
        // get the mesh
        $trustedServers = $this->trustedServers->getServers();
        $wayfList = [];
        foreach ($trustedServers as $i => $server) {
            $host = parse_url($server['url'], PHP_URL_HOST);
            // @TODO: check if the server supports the invitation workflow
            // ...
            $appName = $this->appName;
            $acceptInviteEndpoint = trim(RDMeshService::ENDPOINT_HANDLE_INVITE, '/');
            $senderDomain = RDMeshService::PARAM_NAME_SENDER_DOMAIN;
            $senderDomainValue = $this->request->getParam($senderDomain);
            $token = RDMeshService::PARAM_NAME_TOKEN;
            $tokenValue = $this->request->getParam($token);
            $link = "https://$host/apps/$appName/$acceptInviteEndpoint?$senderDomain=$senderDomainValue&$token=$tokenValue";
            $wayfList[$i] = $link;
        }
        return $wayfList;
    }

    /**
     * Provides the caller with a WAYF page of mesh EFSS providers.
     * @NoCSRFRequired
     * @PublicPage
     */
    public function forwardInvite() {
        // TODO: delegate to the WAYF page controller
        return [
            'WAYF' => 'To continue, please choose your provider from the list and follow instructions.',
            'please choose' => $this->getWAYF(),
        ];
    }
}