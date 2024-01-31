<?php

namespace OCA\Invitation;

use OCA\Invitation\AppInfo\InvitationApp;

/**
 * This is the class with which you define an external api route.
 */

class ExternalApiRoute
{
    private string $method;
    private string $route;
    private string $apiCall;
    private $controller;

    public function __construct($method, $route, $apiCall, $controller)
    {
        $this->method = $method;
        $this->route = $route;
        $this->apiCall = $apiCall;
        $this->controller = $controller;
        $this->register();
    }

    private function register()
    {
        \OCP\API::register(
            $this->method,
            $this->route,
            function ($urlParameters) {
                $params = \OC::$server->getRequest()->getParams();
                return new \OC\OCS\Result(call_user_func_array([$this->controller, $this->apiCall], $params)->getData());
            },
            InvitationApp::APP_NAME
        );
    }
}
