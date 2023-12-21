<?php

/**
 * This is the mesh registry controller.
 *
 */

namespace OCA\Invitation\Controller;

use Exception;
use OCA\Invitation\AppInfo\AppError;
use OCA\Invitation\AppInfo\InvitationApp;
use OCA\Invitation\Db\Schema;
use OCA\Invitation\HttpClient;
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
     * Provides the caller with a list (WAYF page) of mesh EFSS invitation service providers to choose from.

     * @NoCSRFRequired
     * @PublicPage
     *
     * @param string $token the token
     * @param string $providerEndpoint the endpoint of the sender
     * @param string $name the name of the sender
     * @return Response
     */
    public function forwardInvite(string $token = '', string $providerEndpoint = '', string $name = ''): Response
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
        if ($providerEndpoint == '') {
            \OC::$server->getLogger()->error('Invite is missing the invitation service provider endpoint.', ['app' => InvitationApp::APP_NAME]);
            return new RedirectResponse(
                $urlGenerator->linkToRoute(
                    InvitationApp::APP_NAME . '.error.invitation',
                    [
                        'message' => AppError::HANDLE_INVITATION_MISSING_PROVIDER_ENDPOINT
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

        if (!$this->meshRegistryService->isKnowInvitationServiceProvider($providerEndpoint)) {
            \OC::$server->getLogger()->error("Invitation service provider endpoint '$providerEndpoint' is unknown.", ['app' => InvitationApp::APP_NAME]);
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
            MeshRegistryService::PARAM_NAME_PROVIDER_ENDPOINT => $providerEndpoint,
            MeshRegistryService::PARAM_NAME_NAME => $name,
        ];
        return new RedirectResponse(
            $urlGenerator->linkToRoute($this->meshRegistryService->getWayfPageRoute(), $params)
        );
    }

    /**
     * Returns the properties of the this invitation service provider.
     *
     * @PublicPage
     * @NoCSRFRequired
     *
     * @return DataResponse ['data' => :InvitationServiceProvider]
     */
    public function invitationServiceProvider(): DataResponse
    {
        try {
            $isp = $this->meshRegistryService->getInvitationServiceProvider();
            $properties = [];
            $properties['name'] = $isp->getName();
            $properties['endpoint'] = $isp->getEndpoint();
            $properties['domain'] = $isp->getDomain();
            return new DataResponse(
                [
                    'success' => true,
                    'data' => $properties,
                ],
                Http::STATUS_OK,
            );
        } catch (NotFoundException $e) {
            $this->logger->error($e->getMessage() . ' Stacktrace: ' . $e->getTraceAsString(), ['app' => InvitationApp::APP_NAME]);
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
     * Updates this instance's invitation service provider.
     *
     * @NoCSRFRequired
     *
     * @param $endpoint the endpoint if the provider to update
     * @param $fields the update fields, allowed are 'endpoint', 'name', 'domain'
     * @return DataResponse
     */
    public function updateInvitationServiceProvider(string $endpoint, array $fields): DataResponse
    {
        try {
            if (!isset($endpoint) || trim($endpoint) === '') {
                return new DataResponse(
                    [
                        'success' => false,
                        'error_message' => AppError::MESH_REGISTRY_UPDATE_PROVIDER_ENDPOINT_NOT_SET_ERROR,
                    ],
                    Http::STATUS_NOT_FOUND,
                );
            }
            if ($endpoint === $this->meshRegistryService->getEndpoint()) {
                $this->logger->error("The route is not allowed for updating this instance's invitation service provider", ['app' => InvitationApp::APP_NAME]);
                return new DataResponse(
                    [
                        'success' => false,
                        'error_message' => AppError::MESH_REGISTRY_UPDATE_PROVIDER_ROUTE_NOT_ALLOWED_ERROR,
                    ],
                    Http::STATUS_NOT_FOUND,
                );
            }

            $fieldsArray = [];
            foreach (array_values($fields) as $array) {
                $fieldsArray[array_keys($array)[0]] = array_values($array)[0];
            }

            $isp = $this->meshRegistryService->updateInvitationServiceProvider($endpoint, $fieldsArray);

            return new DataResponse(
                [
                    'success' => true,
                    'data' => $isp,
                ],
                Http::STATUS_OK,
            );
        } catch (Exception $e) {
            $this->logger->error($e->getMessage() . " Stacktrace: " . $e->getTraceAsString(), ['app' => InvitationApp::APP_NAME]);
            return new DataResponse(
                [
                    'success' => false,
                    'error_message' => AppError::MESH_REGISTRY_SET_ENDPOINT_ERROR,
                ],
                Http::STATUS_NOT_FOUND,
            );
        }
    }

    /**
     * Returns all registered invitation service providers.
     *
     * @NoCSRFRequired
     * @PublicPage
     * @return DataResponse ['data' => [:InvitationServiceProvider](an array of InvitationServiceProvider objects)]
     */
    public function invitationServiceProviders(): DataResponse
    {
        try {
            $providers = $this->meshRegistryService->allInvitationServiceProviders();
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
     * Adds a new invitation service provider with the specified endpoint.
     * The properties of the provider will be requested through the specified endpoint.
     * If this fails an HTTP error will be returned.
     *
     * Note: if the provider already exists it's properties will become updated
     * through the remote provider /registry/invitation-service-provider call.
     *
     * @NoCSRFRequired
     *
     * @param string $endpoint the endpoint of the new invitation service provider
     * @return DataResponse [ ..., 'data' => :InvitationServiceProvider ]
     */
    public function addInvitationServiceProvider(string $endpoint): DataResponse
    {
        try {
            $invitationServiceProvider = $this->meshRegistryService->addInvitationServiceProvider($endpoint);

            $url = $this->meshRegistryService->getFullInvitationServiceProviderEndpointUrl($endpoint);
            $httpClient = new HttpClient();
            $response = $httpClient->curlGet($url);
            if ($response['success'] == false) {
                $this->logger->error('Failed to call ' . MeshRegistryService::ENDPOINT_INVITATION_SERVICE_PROVIDER . " on endpoint '$endpoint'. Response: " . print_r($response, true), ['app' => InvitationApp::APP_NAME]);
                throw new ServiceException("Failed to call endpoint '$endpoint'");
            }

            $result = (array)$response['response'];
            if (!isset($result['success']) || $result['success'] == false) {
                throw new ServiceException(AppError::MESH_REGISTRY_ENDPOINT_INVITATION_SERVICE_PROVIDER_RESPONSE_INVALID);
            }
            $verifiedResponse = $this->verifyInvitationServiceProviderResponse((array)$result['data'])->getData();
            if ($verifiedResponse['success'] == true) {
                // update the provider with the returned properties. Note: with the exception of the endpoint
                $invitationServiceProvider = $this->meshRegistryService->updateInvitationServiceProvider($endpoint, [
                    Schema::INVITATION_SERVICE_PROVIDER_DOMAIN => $verifiedResponse['data'][Schema::INVITATION_SERVICE_PROVIDER_DOMAIN],
                    Schema::INVITATION_SERVICE_PROVIDER_NAME => $verifiedResponse['data'][Schema::INVITATION_SERVICE_PROVIDER_NAME],
                ]);
                return new DataResponse(
                    [
                        'success' => true,
                        'data' => $invitationServiceProvider,
                    ]
                );
            }

            throw new ServiceException(AppError::MESH_REGISTRY_ENDPOINT_INVITATION_SERVICE_PROVIDER_RESPONSE_INVALID);
        } catch (ServiceException $e) {
            $this->logger->error($e->getMessage() . ' Stacktrace: ' . $e->getTraceAsString(), ['app' => InvitationApp::APP_NAME]);
            // try to delete the previously inserted new provider
            $this->meshRegistryService->deleteInvitationServiceProvider($endpoint);
            return new DataResponse(
                [
                    'success' => false,
                    'error_message' => AppError::MESH_REGISTRY_ADD_PROVIDER_ERROR,
                ],
                Http::STATUS_NOT_FOUND,
            );
        } catch (Exception $e) {
            $this->logger->error($e->getMessage() . ' Stacktrace: ' . $e->getTraceAsString(), ['app' => InvitationApp::APP_NAME]);
            // final effort trying to delete the previously inserted new provider
            $this->meshRegistryService->deleteInvitationServiceProvider($endpoint);
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
     * Returns a DataResponse object that informs about the result of the verification:
     * DataResponse:
     *  [
     *      'success' => true|false,
     *          one of:
     *      'data' => $params (in case of success)
     *      'error_message': '...MESSAGE' (in case of failure)
     *  ]
     *
     * @param array $params
     * @return DataResponse
     */
    private function verifyInvitationServiceProviderResponse(array $params): DataResponse
    {
        if (
            is_array($params)
            && isset($params[Schema::INVITATION_SERVICE_PROVIDER_ENDPOINT])
            && isset($params[Schema::INVITATION_SERVICE_PROVIDER_DOMAIN])
            && isset($params[Schema::INVITATION_SERVICE_PROVIDER_NAME])
        ) {
            return new DataResponse([
                'success' => true,
                'data' => $params
            ], Http::STATUS_OK);
        }
        $this->logger->error('Could not validate the response fields. Fields: ' . print_r($params, true), ['app' => InvitationApp::APP_NAME]);
        return new DataResponse([
            'success' => false,
            'error_message' => AppError::MESH_REGISTRY_ENDPOINT_INVITATION_SERVICE_PROVIDER_RESPONSE_INVALID,
        ], Http::STATUS_NOT_FOUND);
    }

    /**
     * Deletes the invitation service provider with the specified endpoint.
     *
     * @NoCSRFRequired
     *
     * @param string $endpoint the endpoint of the invitation service provider to delete
     * @return DataResponse
     */
    public function deleteInvitationServiceProvider(string $endpoint): DataResponse
    {
        try {
            $invitationServiceProvider = $this->meshRegistryService->deleteInvitationServiceProvider($endpoint);
            return new DataResponse(
                [
                    'success' => isset($invitationServiceProvider) ? true : false,
                ],
                Http::STATUS_OK,
            );
        } catch (ServiceException $e) {
            $this->logger->error($e, ['app' => InvitationApp::APP_NAME]);
            return new DataResponse(
                [
                    'success' => false,
                    'error_message' => AppError::MESH_REGISTRY_DELETE_PROVIDER_ERROR,
                ],
                Http::STATUS_NOT_FOUND,
            );
        }
    }

    /**
     * Whether only sharing with invited users is allowed.
     *
     * @NoCSRFRequired
     *
     * @param bool $allow
     * @return DataResponse
     */
    public function setAllowSharingWithInvitedUsersOnly(bool $allow): DataResponse
    {
        try {
            $result = $this->meshRegistryService->setAllowSharingWithInvitedUsersOnly(boolval($allow));
            return new DataResponse(
                [
                    'success' => true,
                    'data' => $result,
                ],
                Http::STATUS_OK
            );
        } catch (Exception $e) {
            $this->logger->error("Unable to set 'allow_sharing_with_invited_users_only' config param. " . $e->getMessage() . ' Stacktrace: ' . $e->getTraceAsString(), ['app' => InvitationApp::APP_NAME]);
            return new DataResponse(
                [
                    'success' => false,
                    'error_message' => AppError::MESH_REGISTRY_SET_ALLOW_SHARING_WITH_INVITED_USERS_ONLY_ERROR
                ],
                Http::STATUS_NOT_FOUND
            );
        }
    }

    /**
     * Returnes this instance's invitation service provider endpoint.
     *
     * @NoCSRFRequired
     *
     * @return DataResponse
     */
    public function getEndpoint(): DataResponse
    {
        try {
            $endpoint = $this->meshRegistryService->getEndpoint();
            return new DataResponse(
                [
                    'success' => true,
                    'data' => $endpoint,
                ],
                Http::STATUS_OK,
            );
        } catch (ServiceException $e) {
            return new DataResponse(
                [
                    'success' => false,
                    'error_message' => AppError::MESH_REGISTRY_GET_ENDPOINT_ERROR,
                ],
                Http::STATUS_NOT_FOUND,
            );
        }
    }

    /**
     * Sets the endpoint of this invitation service provider
     *
     * @NoCSRFRequired
     *
     * @param string $endpoint
     * @return DataResponse
     */
    public function setEndpoint(string $endpoint): DataResponse
    {
        try {
            $endpoint = $this->meshRegistryService->setEndpoint($endpoint);
            return new DataResponse(
                [
                    'success' => true,
                    'data' => $endpoint,
                ],
                Http::STATUS_OK,
            );
        } catch (ServiceException $e) {
            $this->logger->error($e->getMessage() . ' Stacktrace: ' . $e->getTraceAsString(), ['app' => InvitationApp::APP_NAME]);
            return new DataResponse(
                [
                    'success' => false,
                    'error_message' => AppError::MESH_REGISTRY_GET_ENDPOINT_ERROR,
                ],
                Http::STATUS_NOT_FOUND,
            );
        }
    }

    /**
     * Returnes this instance's invitation service provider name.
     *
     * @NoCSRFRequired
     *
     * @return DataResponse
     */
    public function getName(): DataResponse
    {
        try {
            $name = $this->meshRegistryService->getName();
            return new DataResponse(
                [
                    'success' => true,
                    'data' => $name,
                ],
                Http::STATUS_OK,
            );
        } catch (ServiceException $e) {
            return new DataResponse(
                [
                    'success' => false,
                    'error_message' => AppError::MESH_REGISTRY_GET_NAME_ERROR,
                ],
                Http::STATUS_NOT_FOUND,
            );
        }
    }

    /**
     * Sets this instance's invitation service provider name.
     *
     * @NoCSRFRequired
     *
     * @param string $name
     * @return DataResponse
     */
    public function setName(string $name): DataResponse
    {
        try {
            $name = $this->meshRegistryService->setName($name);
            return new DataResponse(
                [
                    'success' => true,
                    'data' => $name,
                ],
                Http::STATUS_OK,
            );
        } catch (ServiceException $e) {
            $this->logger->error($e->getMessage() . ' Stacktrace: ' . $e->getTraceAsString(), ['app' => InvitationApp::APP_NAME]);
            return new DataResponse(
                [
                    'success' => false,
                    'error_message' => AppError::MESH_REGISTRY_SET_NAME_ERROR,
                ],
                Http::STATUS_NOT_FOUND,
            );
        }
    }

    /**
     * Whether only sharing with invited users is allowed.
     *
     * @NoCSRFRequired
     *
     * @return DataResponse
     */
    public function getAllowSharingWithInvitedUsersOnly(): DataResponse
    {
        try {
            $result = $this->meshRegistryService->getAllowSharingWithInvitedUsersOnly();
            return new DataResponse(
                [
                    'success' => true,
                    'data' => $result,
                ],
                Http::STATUS_OK
            );
        } catch (Exception $e) {
            $this->logger->error("Unable to get 'allow_sharing_with_invited_users_only' config param. " . $e->getMessage() . ' Stacktrace: ' . $e->getTraceAsString(), ['app' => InvitationApp::APP_NAME]);
            return new DataResponse(
                [
                    'success' => false,
                    'error_message' => AppError::MESH_REGISTRY_SET_ALLOW_SHARING_WITH_INVITED_USERS_ONLY_ERROR
                ],
                Http::STATUS_NOT_FOUND
            );
        }
    }
}
