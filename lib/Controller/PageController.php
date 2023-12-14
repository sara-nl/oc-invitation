<?php

/**
 * Page controller
 */

namespace OCA\Invitation\Controller;

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
        $allWAYFURLs = null;
        try {
            $allWAYFURLs = $this->getWAYFURLs($token, $providerEndpoint, $name);
        } catch (ServiceException $e) {
            $this->logger->error($e->getMessage() . ' Trace: ' . $e->getTraceAsString(), ['app' => InvitationApp::APP_NAME]);
            echo "Could not display WAYF page.";
            exit(0);
        }
        // TODO: use template for this
        echo '<html title="WAYF"><head></head><h4>Where Are You From</h4>';
        foreach ($allWAYFURLs as $i => $url) {
            $endpoint = parse_url($url, PHP_URL_HOST);
            echo print_r("<p><a href=\"$url\">$endpoint</a></p>", true) . '</html>';
        }
        echo '</html>';

        exit(0);
    }

    /**
     * Returns the WAYF URLs.\
     *
     * @throws ServiceException
     */
    private function getWAYFURLs(string $token, string $providerEndpoint, string $name): array
    {
        $invitationServiceProviders = $this->meshRegistryService->allInvitationServiceProviders();
        $wayfList = [];
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
                $wayfList[$i] = $link;
            }
        }
        return $wayfList;
    }
}
