<?php

/**
 * This is the mesh registry controller.
 *
 */

namespace OCA\Invitation\Controller;

use OCA\Invitation\AppInfo\AppError;
use OCA\Invitation\AppInfo\InvitationApp;
use OCA\Invitation\Service\MeshRegistry\MeshRegistryService;
use OCA\Invitation\Service\NotFoundException;
use OCA\Invitation\Service\ServiceException;
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
     * @return Response
     */
    public function forwardInvite(string $token = '', string $providerDomain = '', string $name = ''): Response
    {
        $urlGenerator = \OC::$server->getURLGenerator();

        if ($token == '') {
            \OC::$server->getLogger()->error('Invite is missing the token.', ['app' => InvitationApp::APP_NAME]);
            return new RedirectResponse(
                $urlGenerator->linkToRoute(
                    InvitationApp::APP_NAME . '.error.invitation',
                    [
                        'message' => AppError::HANDLE_INVITATION_MISSING_TOKEN
                    ]
                )
            );
        }
        if ($providerDomain == '') {
            \OC::$server->getLogger()->error('Invite is missing the provider domain.', ['app' => InvitationApp::APP_NAME]);
            return new RedirectResponse(
                $urlGenerator->linkToRoute(
                    InvitationApp::APP_NAME . '.error.invitation',
                    [
                        'message' => AppError::HANDLE_INVITATION_MISSING_PROVIDER_DOMAIN
                    ]
                )
            );
        }
        if ($name == '') {
            \OC::$server->getLogger()->error('Invite is missing the sender name.', ['app' => InvitationApp::APP_NAME]);
            return new RedirectResponse(
                $urlGenerator->linkToRoute(
                    InvitationApp::APP_NAME . '.error.invitation',
                    [
                        'message' => AppError::HANDLE_INVITATION_MISSING_SENDER_NAME
                    ]
                )
            );
        }

        if (!$this->meshRegistryService->isKnowDomainProvider($providerDomain)) {
            \OC::$server->getLogger()->error("Provider domain '$providerDomain' is unknown.", ['app' => InvitationApp::APP_NAME]);
            return new RedirectResponse(
                $urlGenerator->linkToRoute(
                    InvitationApp::APP_NAME . '.error.invitation',
                    [
                        'message' => AppError::HANDLE_INVITATION_PROVIDER_UNKNOWN
                    ]
                )
            );
        }

        $urlGenerator = \OC::$server->getURLGenerator();
        $params = [
            MeshRegistryService::PARAM_NAME_TOKEN => $token,
            MeshRegistryService::PARAM_NAME_PROVIDER_DOMAIN => $providerDomain,
            MeshRegistryService::PARAM_NAME_NAME => $name,
        ];
        return new RedirectResponse(
            $urlGenerator->linkToRoute($this->meshRegistryService->getWayfPageRoute(), $params)
        );
    }

    /**
     * Returns the domain provider of this instance.
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     * @return DataResponse ['data' => :DomainProvider]
     */
    public function domainProvider(): DataResponse
    {
        try {
            $domainProvider = $this->meshRegistryService->getDomainProvider();
            return new DataResponse(
                [
                    'success' => true,
                    'data' => $domainProvider
                ],
                Http::STATUS_OK,
            );
        } catch (NotFoundException $e) {
            return new DataResponse(
                [
                    'success' => false,
                    'error_message' => AppError::MESH_REGISTRY_GET_PROVIDER_ERROR,
                ],
                Http::STATUS_NOT_FOUND,
            );
        }
    }

    /**
     * Returnes this instance's domain.
     *
     * @NoCSRFRequired
     *
     * @return DataResponse
     */
    public function getDomain(): DataResponse
    {
        try {
            $domain = $this->meshRegistryService->getDomain();
            return new DataResponse(
                [
                    'success' => true,
                    'data' => $domain,
                ],
                Http::STATUS_OK,
            );
        } catch (ServiceException $e) {
            return new DataResponse(
                [
                    'success' => false,
                    'error_message' => AppError::MESH_REGISTRY_GET_DOMAIN_ERROR,
                ],
                Http::STATUS_NOT_FOUND,
            );
        }
    }

    /**
     * Updates the domain of this instance's domain provider to the specified domain.
     *
     * @NoCSRFRequired
     *
     * @param $domain
     * @return DataResponse
     */
    public function setDomain(string $domain): DataResponse
    {
        try {
            $domain = $this->meshRegistryService->setDomain($domain);
            return new DataResponse(
                [
                    'success' => true,
                    'data' => $domain,
                ],
                Http::STATUS_OK,
            );
        } catch (ServiceException $e) {
            return new DataResponse(
                [
                    'success' => false,
                    'error_message' => AppError::MESH_REGISTRY_SET_DOMAIN_ERROR,
                ],
                Http::STATUS_NOT_FOUND,
            );
        }
    }

    /**
     * Returns all registered domain providers.
     *
     * @NoCSRFRequired
     * @PublicPage
     * @return DataResponse ['data' => [:DomainProvider](an array of DomainProvider objects)]
     */
    public function domainProviders(): DataResponse
    {
        try {
            $providers = $this->meshRegistryService->allDomainProviders();
            return new DataResponse(
                [
                    'success' => true,
                    'data' => $providers,
                ],
                Http::STATUS_OK,
            );
        } catch (ServiceException $e) {
            $this->logger->error($e, ['app' => InvitationApp::APP_NAME]);
            return new DataResponse(
                [
                    'success' => false,
                    'error_message' => AppError::MESH_REGISTRY_ALL_PROVIDERS_ERROR,
                ],
                Http::STATUS_NOT_FOUND,
            );
        }
    }

    /**
     * Adds a new domain provider with the specified domain.
     *
     * @NoCSRFRequired
     *
     * @param string $domain the domain of the new domain provider
     * @return DataResponse [ ..., 'data' => :DomainProvider ]
     */
    public function addDomainProvider(string $domain): DataResponse
    {
        try {
            $domainProvider = $this->meshRegistryService->addDomainProvider($domain);
            return new DataResponse(
                [
                    'success' => true,
                    'data' => $domainProvider,
                ],
                Http::STATUS_OK,
            );
        } catch (ServiceException $e) {
            $this->logger->error($e, ['app' => InvitationApp::APP_NAME]);
            return new DataResponse(
                [
                    'success' => false,
                    'error_message' => AppError::MESH_REGISTRY_ADD_PROVIDER_ERROR,
                ],
                Http::STATUS_NOT_FOUND,
            );
        }
    }

    /**
     * Adds the domain provider with the specified domain.
     *
     * @NoCSRFRequired
     *
     * @param string $domain the domain of the domain provider to delete
     * @return DataResponse
     */
    public function deleteDomainProvider(string $domain): DataResponse
    {
        try {
            $domainProvider = $this->meshRegistryService->deleteDomainProvider($domain);
            return new DataResponse(
                [
                    'success' => isset($domainProvider) ? true : false,
                ],
                Http::STATUS_OK,
            );
        } catch (ServiceException $e) {
            $this->logger->error($e, ['app' => InvitationApp::APP_NAME]);
            return new DataResponse(
                [
                    'success' => false,
                    'error_message' => AppError::MESH_REGISTRY_ADD_PROVIDER_ERROR,
                ],
                Http::STATUS_NOT_FOUND,
            );
        }
    }
}
