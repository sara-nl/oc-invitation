<?php

namespace OCA\Collaboration;

use OCA\Collaboration\AppInfo\CollaborationApp;
use OCP\AppFramework\Controller;

/**
 * This is the class with which you define an external api route.
 */

class ExternalApiRoute
{
    private string $method;
    private string $route;
    private string $apiCall;
    private Controller $controller;

    /**
     * @param string $method the route's http method
     * @param string $route the route
     * @param string $apiCall the controller method
     * @param Controller $controller the controller
     * @param bool $isRedirectResponse whether the response of the controller is of type RedirectResponse in which case there is no data to return
     */
    public function __construct(string $method, string $route, string $apiCall, Controller $controller, bool $isRedirectResponse = false)
    {
        $this->method = $method;
        $this->route = $route;
        $this->apiCall = $apiCall;
        $this->controller = $controller;
        if ($isRedirectResponse) {
            $this->registerRedirectResponse();
        } else {
            $this->register();
        }
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
            CollaborationApp::APP_NAME
        );
    }

    private function registerRedirectResponse()
    {
        \OCP\API::register(
            $this->method,
            $this->route,
            function ($urlParameters) {
                $params = \OC::$server->getRequest()->getParams();
                return new \OC\OCS\Result(call_user_func_array([$this->controller, $this->apiCall], $params)->getStatus());
            },
            CollaborationApp::APP_NAME
        );
    }
}
