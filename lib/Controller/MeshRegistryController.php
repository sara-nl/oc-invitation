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
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Http\RedirectResponse;
use OCP\AppFramework\Http\Response;
use OCP\IRequest;

class MeshRegistryController extends Controller
{

    private MeshService $meshService;


    public function __construct($appName, IRequest $request, MeshService $meshService)
    {
        parent::__construct($appName, $request);
        $this->meshService = $meshService;
    }

    /**
     * Provides the caller with a WAYF page of mesh EFSS providers.

     * @NoCSRFRequired
     * @PublicPage
     * @param string $token the token
     * @param string $senderDomain the domain of the sender
     * @param string $senderEmail the email of the sender
     * TODO: check the response type
     * @return Response
     */
    public function forwardInvite(string $token = '', string $providerDomain = ''): Response
    {
        if ($token == '') {
            return new DataResponse(
                ['error' => 'token missing'],
                Http::STATUS_NOT_FOUND
            );
        }
        if ($providerDomain == '') {
            return new DataResponse(
                ['error' => 'provider domain missing'],
                Http::STATUS_NOT_FOUND
            );
        }

        $urlGenerator = \OC::$server->getURLGenerator();
        $params = [
            MeshService::PARAM_NAME_TOKEN => $token,
            MeshService::PARAM_NAME_PROVIDER_DOMAIN => $providerDomain,
        ];
        return new RedirectResponse($urlGenerator->linkToRoute($this->meshService->getWayfPageRoute(), $params));
    }
}
