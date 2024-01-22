<?php

/**
 * Page controller
 */

namespace OCA\Invitation\Controller;

use Exception;
use OCA\Invitation\AppInfo\AppError;
use OCA\Invitation\AppInfo\InvitationApp;
use OCA\Invitation\Service\MeshRegistry\MeshRegistryService;
use OCA\Invitation\Service\ServiceException;
use OCP\AppFramework\Controller;
use OCP\ILogger;
use OCP\IRequest;

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
     * @return void
     */
    public function wayf(string $token, string $providerEndpoint, string $name): void
    {
        // FIXME: use template for this

        $title = '<html title="WAYF"><head></head><h4>Where Are You From</h4>';
        try {
            $wayfItems = $this->getWayfItems($token, $providerEndpoint, $name);
            $this->logger->debug(print_r($wayfItems, true));
            if (sizeof($wayfItems) == 0) {
                throw new ServiceException(AppError::WAYF_NO_PROVIDERS_FOUND);
            }
            $html = $title;
            foreach ($wayfItems as $wayfItem) {
                $this->logger->debug(print_r($wayfItem, true));
                $url = $wayfItem['handleInviteUrl'];
                $name = $wayfItem['providerName'];
                $html .= print_r("<p><a href=\"$url\">$name</a></p>", true) . '</html>';
            }
            $html .= '</html>';
            echo $html;
        } catch (ServiceException $e) {
            $this->logger->error($e->getMessage() . ' Trace: ' . $e->getTraceAsString(), ['app' => InvitationApp::APP_NAME]);
            $html = $title;
            $html .= '<div>' . $e->getMessage() . '</div></html>';
            echo $html;
        } catch (Exception $e) {
            $this->logger->error($e->getMessage() . ' Trace: ' . $e->getTraceAsString(), ['app' => InvitationApp::APP_NAME]);
            $html = $title;
            $html .= '<div>' . AppError::WAYF_ERROR . '</div></html>';
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
     * @param string $name
     * @return array
     * @throws ServiceException
     */
    private function getWayfItems(string $token, string $providerEndpoint, string $name): array
    {
        try {
            $invitationServiceProviders = $this->meshRegistryService->allInvitationServiceProviders();
            $wayfItems = [];
            foreach ($invitationServiceProviders as $i => $invitationServiceProvider) {
                if ($invitationServiceProvider->getEndpoint() != $this->meshRegistryService->getEndpoint()) {
                    // TODO: optional: check if the server supports the invitation workflow
                    //       This should be done via the ocm /ocm-provider endpoint which must return the '/invite-accepted' capability
                    //       to inform us it supports handling invitations.
                    //       More likely is that we already know it should,
                    //       so this would be more like a sanity check (eg. the service may be down)

                    $serviceEndpoint = $invitationServiceProvider->getEndpoint();
                    $handleInviteEndpoint = trim(MeshRegistryService::ENDPOINT_HANDLE_INVITE, '/');
                    $tokenParam = MeshRegistryService::PARAM_NAME_TOKEN;
                    $providerEndpointParam = MeshRegistryService::PARAM_NAME_PROVIDER_ENDPOINT;
                    $nameParam = MeshRegistryService::PARAM_NAME_NAME;
                    $link = "$serviceEndpoint/$handleInviteEndpoint?$tokenParam=$token&$providerEndpointParam=$providerEndpoint&$nameParam=$name";
                    $wayfItems[$i] = [
                        "handleInviteUrl" => $link,
                        "providerName" => $invitationServiceProvider->getName(),
                    ];
                }
            }
            return $wayfItems;
        } catch (Exception $e) {
            $this->logger->error($e->getMessage() . ' Trace: ' . $e->getTraceAsString(), ['app' => InvitationApp::APP_NAME]);
            throw new ServiceException(AppError::WAYF_ERROR);
        }
    }
}
