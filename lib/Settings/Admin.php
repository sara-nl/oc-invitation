<?php

namespace OCA\Invitation\Settings;


use OCP\Settings\ISettings;
use OCP\Template;

class Admin implements ISettings
{

    public  function __construct()
    {

    }

    /**
     * The panel controller method that returns a template to the UI
     * @since 10.0
     * @return TemplateResponse | Template
     */
    public function getPanel()
    {
		$template = new Template('invitation', 'settings/admin');
		$template->assign('domain', );
		$template->assign('allow_sharing_with_non_invited_users', $trustedServers->getAutoAddServers());
		return $template;
    }

    /**
     * A string to identify the section in the UI / HTML and URL
     * @since 10.0
     * @return string
     */
    public function getSectionID(): string
    {
        return 'invitation';
    }

    /**
     * The number used to order the section in the UI.
     * @since 10.0
     * @return int between 0 and 100, with 100 being the highest priority
     */
    public function getPriority(): int
    {
        return "20";
    }
}
