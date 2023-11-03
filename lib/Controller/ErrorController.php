<?php

/**
 * 
 */

namespace OCA\RDMesh\Controller;

use OCA\RDMesh\AppInfo\RDMesh;
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
        return new TemplateResponse(RDMesh::APP_NAME, 'error', ['message' => $message]);
    }

}
