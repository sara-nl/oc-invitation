<?php

/**
 * Page controller
 */

namespace OCA\Collaboration\Controller;

use Exception;
use OCA\Collaboration\AppInfo\AppError;
use OCA\Collaboration\AppInfo\CollaborationApp;
use OCA\Collaboration\Service\MeshRegistry\MeshRegistryService;
use OCA\Collaboration\Service\ServiceException;
use OCP\AppFramework\Controller;
use OCP\ILogger;
use OCP\IRequest;
use OCP\Template;

class PageController extends Controller
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
     * Displays the WAYF page.
     *
     * @NoCSRFRequired
     * @PublicPage
     * @param string $token the token
     * @param string $providerEndpoint the endpoint of the sender
     * @return
     */
    public function wayf(string $token, string $providerEndpoint): void
    {
        try {
            $wayfItems = $this->getWayfItems($token, $providerEndpoint);
            if (sizeof($wayfItems) == 0) {
                throw new ServiceException(AppError::WAYF_NO_PROVIDERS_FOUND);
            }
            $l = \OC::$server->getL10NFactory()->findLanguage(CollaborationApp::APP_NAME);
            $tmpl = new Template('collaboration', "wayf/wayf", '', false, $l);
            $tmpl->assign('wayfItems', $wayfItems);
            echo $tmpl->fetchPage();
        } catch (ServiceException $e) {
            $this->logger->error($e->getMessage() . ' Trace: ' . $e->getTraceAsString(), ['app' => CollaborationApp::APP_NAME]);
            $html = '<div>' . $e->getMessage() . '</div></html>';
            echo $html;
        } catch (Exception $e) {
            $this->logger->error($e->getMessage() . ' Trace: ' . $e->getTraceAsString(), ['app' => CollaborationApp::APP_NAME]);
            $html = '<div>' . AppError::WAYF_ERROR . '</div></html>';
            echo $html;
        }
        exit(0);
    }

    /**
     * Returns an array from which the WAYF page can be build in the following format:
     * [
     *      [
     *          "handleInviteUrl" => url,
     *          "providerName" => providerName
     *      ],
     *      [
     *          ...
     *      ]
     * ]
     *
     * @param string $token
     * @param string $providerEndpoint
     * @return array
     * @throws ServiceException
     */
    private function getWayfItems(string $token, string $providerEndpoint): array
    {
        try {
            $invitationServiceProviders = $this->meshRegistryService->allInvitationServiceProviders();
            $wayfItems = [];
            foreach ($invitationServiceProviders as $i => $invitationServiceProvider) {
                if ($invitationServiceProvider->getEndpoint() != $this->meshRegistryService->getEndpoint()) {
                    $serviceEndpoint = $invitationServiceProvider->getEndpoint();
                    $handleInviteEndpoint = trim(MeshRegistryService::ENDPOINT_HANDLE_INVITE, '/');
                    $tokenParam = MeshRegistryService::PARAM_NAME_TOKEN;
                    $providerEndpointParam = MeshRegistryService::PARAM_NAME_PROVIDER_ENDPOINT;
                    $link = "$serviceEndpoint/$handleInviteEndpoint?$tokenParam=$token&$providerEndpointParam=$providerEndpoint";
                    // discover url of institute logo
                    $url = parse_url($invitationServiceProvider->getEndpoint());
                    $host = $url['host'];
                    $scheme = $url['scheme'];
                    $logoUrl = "$scheme://$host/dashboard/images/logo.png";
                    $wayfItems[$i] = [
                        "logoUrl" => $logoUrl,
                        "handleInviteUrl" => $link,
                        "providerName" => $invitationServiceProvider->getName(),
                    ];
                }
            }
            return $wayfItems;
        } catch (Exception $e) {
            $this->logger->error($e->getMessage() . ' Trace: ' . $e->getTraceAsString(), ['app' => CollaborationApp::APP_NAME]);
            throw new ServiceException(AppError::WAYF_ERROR);
        }
    }
}
