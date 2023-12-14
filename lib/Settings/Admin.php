<?php

namespace OCA\Invitation\Settings;

use OCA\Invitation\AppInfo\InvitationApp;
use OCA\Invitation\Service\MeshRegistry\MeshRegistryService;
use OCP\ILogger;
use OCP\Settings\ISettings;
use OCP\Template;

class Admin implements ISettings
{
    private MeshRegistryService $meshRegistryService;
    private ILogger $logger;

    public function __construct(MeshRegistryService $meshRegistryService)
    {
        $this->meshRegistryService = $meshRegistryService;
        $this->logger = \OC::$server->getLogger();
    }

    /**
     * The panel controller method that returns a template to the UI
     * @since 10.0
     * @return TemplateResponse | Template
     */
    public function getPanel()
    {
        $template = new Template('invitation', 'settings/admin');
        $template->assign('endpoint', $this->meshRegistryService->getEndpoint());
        $template->assign(InvitationApp::CONFIG_ALLOW_SHARING_WITH_INVITED_USERS_ONLY, $this->meshRegistryService->getAllowSharingWithInvitedUsersOnly());
        return $template;
    }

    /**
     * A string to identify the section in the UI / HTML and URL
     * @since 10.0
     * @return string
     */
    public function getSectionID(): string
    {
        return 'sharing';
    }

    /**
     * The number used to order the section in the UI.
     * @since 10.0
     * @return int between 0 and 100, with 100 being the highest priority
     */
    public function getPriority(): int
    {
        return 30;
    }
}
