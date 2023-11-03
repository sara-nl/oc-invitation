<?php

/**
 * This is the mesh registry controller.
 * Endpoints:
 *      /get-domain
 *      /forward-invite
 * 
 */

namespace OCA\RDMesh\Controller;

use OCA\RDMesh\AppInfo\AppError;
use OCA\RDMesh\AppInfo\RDMesh;
use OCA\RDMesh\Service\MeshRegistryService;
use OCA\RDMesh\Service\NotFoundException;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Http\RedirectResponse;
use OCP\AppFramework\Http\Response;
use OCP\ILogger;
use OCP\IRequest;

class MeshRegistryController extends Controller
{

    private MeshRegistryService $meshRegistryService;
    private ILogger $logger;


    public function __construct($appName, IRequest $request, MeshRegistryService $meshRegistryService)
    {
        parent::__construct($appName, $request);
        $this->meshRegistryService = $meshRegistryService;
        $this->logger = \OC::$server->getLogger();
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
            MeshRegistryService::PARAM_NAME_TOKEN => $token,
            MeshRegistryService::PARAM_NAME_PROVIDER_DOMAIN => $providerDomain,
        ];
        return new RedirectResponse($urlGenerator->linkToRoute($this->meshRegistryService->getWayfPageRoute(), $params));
    }

    /**
     * Returns all registered domain providers.
     * 
     * @NoCSRFRequired
     * @PublicPage
     * @return DataResponse
     */
    public function providers(): DataResponse
    {
        try {
            $providers = $this->meshRegistryService->allDomainProviders();
            return new DataResponse(
                [
                    'success' => true,
                    'providers' => $providers,
                ],
                Http::STATUS_OK,
            );
        } catch (NotFoundException $e) {
            $this->logger->error($e, ['app' => RDMesh::APP_NAME]);
            return new DataResponse(
                [
                    'success' => true,
                    'error_message' => AppError::MESH_REGISTRY_ALL_PROVIDERS_ERROR,
                ],
                Http::STATUS_NOT_FOUND,
            );
        }
    }
}
