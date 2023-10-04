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
     */
    public function forwardInvite(string $token = '', string $senderDomain = '', string $senderEmail = '')
    {
        if ($token == '') {
            return new DataResponse(
                ['error' => 'token missing'],
                Http::STATUS_NOT_FOUND
            );
        }
        if ($senderDomain == '') {
            return new DataResponse(
                ['error' => 'sender domain missing'],
                Http::STATUS_NOT_FOUND
            );
        }
        if ($senderEmail == '') {
            return new DataResponse(
                ['error' => 'sender email missing'],
                Http::STATUS_NOT_FOUND
            );
        }

        $urlGenerator = \OC::$server->getURLGenerator();
        $params = [
            MeshService::PARAM_NAME_TOKEN => $token, 
            MeshService::PARAM_NAME_SENDER_DOMAIN => $senderDomain,
            MeshService::PARAM_NAME_SENDER_EMAIL => $senderEmail,
        ];
        return new RedirectResponse($urlGenerator->linkToRoute($this->meshService->getWayfPageRoute(), $params));
    }
}
