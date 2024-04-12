<?php

namespace OCA\Invitation\Settings;

use OCA\Invitation\AppInfo\InvitationApp;
use OCA\Invitation\Service\ApplicationConfigurationException;
use OCA\Invitation\Service\MeshRegistry\MeshRegistryService;
use OCA\Invitation\Service\NotFoundException;
use OCP\Settings\ISettings;
use OCP\Template;

class Admin implements ISettings
{
    private MeshRegistryService $meshRegistryService;

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
        try {
            $invitationServiceProvider = $this->meshRegistryService->getInvitationServiceProvider();
            $template->assign('endpoint', $invitationServiceProvider->getEndpoint());
            $template->assign('name', $invitationServiceProvider->getName());
        } catch (ApplicationConfigurationException $e) {
            $template->assign('endpoint', '');
            $template->assign('name', '');
        } catch (NotFoundException $e) {
            $template->assign('endpoint', '');
            $template->assign('name', '');
        }
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
