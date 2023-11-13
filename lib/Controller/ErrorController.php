<?php

/**
 * Error controller.
 * 
 */

namespace OCA\Invitation\Controller;

use OCA\Invitation\AppInfo\InvitationApp;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IRequest;

class ErrorController extends Controller
{

    public function __construct($appName, IRequest $request)
    {
        parent::__construct($appName, $request);
    }

    /**
     * Displays the invitation error.
     * 
     * @NoCSRFRequired
     * @PublicPage
     * @param string $message
     * @return TemplateResponse
     */
    public function invitation(string $message): TemplateResponse
    {
        return new TemplateResponse(InvitationApp::APP_NAME, 'error', ['message' => $message]);
    }
}
