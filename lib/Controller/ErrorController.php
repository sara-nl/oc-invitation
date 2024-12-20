<?php

/**
 * Error controller.
 *
 */

namespace OCA\Collaboration\Controller;

use OCA\Collaboration\AppInfo\CollaborationApp;
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
    public function invitation(string $message, string $param1 = ""): TemplateResponse
    {
        return new TemplateResponse(CollaborationApp::APP_NAME, 'error', ['message' => $message, 'param1' => $param1]);
    }
}
