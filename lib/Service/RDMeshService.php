<?php

namespace OCA\RDMesh\Service;

use OCP\IConfig;

class RDMeshService {

    private $config;
    private $appName;

    private const ENDPOINT_FORWARD_INVITE = '/registry/forward-invite';
    public const ENDPOINT_ACCEPT_INVITE = '/accept-invite';
    public const ENDPOINT_HANDLE_INVITE = '/handle-invite';
    public const ENDPOINT_INVITE_ACCEPTED = '/ocm/invite-accepted';
    public const PARAM_NAME_SENDER_DOMAIN = 'senderDomain';
    public const PARAM_NAME_RECIPIENT_DOMAIN = 'recipientDomain';
    public const PARAM_NAME_TOKEN = 'token';
    public const PARAM_NAME_RECIPIENT_TOKEN = 'recipientToken';
    public const PARAM_NAME_EMAIL = 'email';

    public function __construct($appName, IConfig $config){
        $this->appName = $appName;
        $this->config = $config;
    }

    /**
     * Returns the full 'https://...host.../forward-invite' endpoint of this EFSS instance
     */
    public function getFullForwardInviteEndpoint() {
        $domain = $this->getDomain();
        $appName = $this->appName;
        $forwardInviteEndpoint = trim(self::ENDPOINT_FORWARD_INVITE, "/");
        return "https://$domain/apps/$appName/$forwardInviteEndpoint";
    }

    /**
     * Returns the full 'https://...host.../forward-invite' endpoint URL of this EFSS instance.
     * 
     */
    public function getFullAcceptInviteEndpointURL() {
        $host = $this->getDomain();
        $appName = $this->appName;
        $acceptInviteEndpoint = trim(self::ENDPOINT_ACCEPT_INVITE, "/");
        return "https://$host/apps/$appName/$acceptInviteEndpoint";
    }

    /**
     * Returns the full 'https://...host.../invite-accepted' endpoint URL of this EFSS instance.
     * 
     */
    public function getFullInviteAcceptedEndpointURL(string $senderHost = "") {
        if ($senderHost == "") {
            return ['error' => 'unable to build full intive-accepted endpoint URL, senderHost not specified'];
        }
        $appName = $this->appName;
        $acceptInviteEndpoint = trim(self::ENDPOINT_INVITE_ACCEPTED, "/");
        return "https://$senderHost/apps/$appName/$acceptInviteEndpoint";
    }

    /**
     * Returns the domain of this mesh node as configured.
     * @NoCSRFRequired
     */
    public function getDomain() {
        $domain = $this->getAppValue('domain');
        return $domain;
    }

    /**
     * Returns the value of the specified application key
     */
    public function getAppValue($key) {
        return $this->config->getAppValue($this->appName, $key);
    }

    /**
     * Sets the value of the specified application key
     */
    public function setAppValue($key, $value) {
        $this->config->setAppValue($this->appName, $key, $value);
    }

}