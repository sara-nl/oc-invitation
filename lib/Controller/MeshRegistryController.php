<?php

/**
 * This is the mesh registry controller.
 * Endpoints:
 *      /get-domain
 *      /forward-invite
 * 
 */

namespace OCA\RDMesh\Controller;

use OCA\RDMesh\Service\MeshService;
use OCA\Federation\TrustedServers;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;

class MeshRegistryController extends Controller
{

    private TrustedServers $trustedServers;


    public function __construct($appName, IRequest $request, TrustedServers $trustedServers)
    {
        parent::__construct($appName, $request);
        $this->trustedServers = $trustedServers;
    }

    /**
     * Returns the WAYF page.
     */
    private function getWAYF(string $token, string $senderDomain)
    {
        // get the mesh
        $trustedServers = $this->trustedServers->getServers();
        $wayfList = [];
        foreach ($trustedServers as $i => $server) {
            $host = parse_url($server['url'], PHP_URL_HOST);
            // TODO: check if the server supports new DataResponse([$invitationLink], Http::STATUS_OK);orkflow
            // 
            $appName = $this->appName;
            $acceptInviteEndpoint = trim(MeshService::ENDPOINT_HANDLE_INVITE, '/');
            $tokenParam = MeshService::PARAM_NAME_TOKEN;
            $senderDomainParam = MeshService::PARAM_NAME_SENDER_DOMAIN;
            $link = "https://$host/apps/$appName/$acceptInviteEndpoint?$tokenParam=$token&$senderDomainParam=$senderDomain";
            $wayfList[$i] = $link;
        }
        return $wayfList;
    }

    /**
     * Provides the caller with a WAYF page of mesh EFSS providers.

     * @NoCSRFRequired
     * @PublicPage
     */
    public function forwardInvite(string $token = '', string $senderDomain = '')
    {
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

        /* TODO: delegate to a WAYF page controller */
        $wayf = $this->getWAYF($token, $senderDomain);

        return new DataResponse(
            [
                'WAYF' => 'To continue, please choose your provider from the list and follow instructions.',
                'please choose' => $wayf,
            ],
            Http::STATUS_OK
        );
    }
}
