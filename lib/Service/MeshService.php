<?php

namespace OCA\RDMesh\Service;

use OCP\IConfig;

class MeshService
{

    private $config;
    private $appName;

    private const ENDPOINT_FORWARD_INVITE = '/registry/forward-invite';
    public const ENDPOINT_ACCEPT_INVITE = '/accept-invite';
    public const ENDPOINT_HANDLE_INVITE = '/handle-invite';
    public const ENDPOINT_INVITE_ACCEPTED = '/ocm/invite-accepted';
    private const ROUTE_PAGE_WAYF = 'page.wayf';
    public const PARAM_NAME_SENDER_DOMAIN = 'senderDomain';
    public const PARAM_NAME_RECIPIENT_PROVIDER = 'recipientProvider';
    public const PARAM_NAME_RECIPIENT_DOMAIN = 'recipientDomain';
    public const PARAM_NAME_TOKEN = 'token';
    public const PARAM_NAME_USER_ID = 'userID';
    public const PARAM_NAME_RECIPIENT_TOKEN = 'recipientToken';
    public const PARAM_NAME_EMAIL = 'email';
    public const PARAM_NAME_SENDER_EMAIL = 'senderEmail';
    public const PARAM_NAME_NAME = 'name';

    public function __construct($appName, IConfig $config)
    {
        $this->appName = $appName;
        $this->config = $config;
    }

    /**
     * Returns the full 'https://...host.../forward-invite' endpoint of this EFSS instance
     * 
     * @return string
     */
    public function getFullForwardInviteEndpoint()
    {
        $domain = $this->getDomain();
        $appName = $this->appName;
        $forwardInviteEndpoint = trim(self::ENDPOINT_FORWARD_INVITE, "/");
        return "https://$domain/apps/$appName/$forwardInviteEndpoint";
    }

    /**
     * 
     * @return string
     */
    public function getWayfPageRoute(): string
    {
        $appName = $this->appName;
        $wayfPageEndpoint = self::ROUTE_PAGE_WAYF;
        return "$appName.$wayfPageEndpoint";
    }

    /**
     * Returns the full 'https://...host.../accept-invite' endpoint URL of this EFSS instance.
     * 
     * @return string the full /accept-invite endpoint URL
     */
    public function getFullAcceptInviteEndpointURL(): string
    {
        $host = $this->getDomain();
        $appName = $this->appName;
        $acceptInviteEndpoint = trim(self::ENDPOINT_ACCEPT_INVITE, "/");
        return "https://$host/apps/$appName/$acceptInviteEndpoint";
    }

    /**
     * Returns the full 'https://...host.../invite-accepted' endpoint URL of this EFSS instance.
     * 
     * @param string $senderHost the host of the sender of the invitation
     * @return string the full /invite-accepted endpoint URL
     */
    public function getFullInviteAcceptedEndpointURL(string $senderHost = ""): string
    {
        if ($senderHost == "") {
            return ['error' => 'unable to build full intive-accepted endpoint URL, senderHost not specified'];
        }
        $appName = $this->appName;
        $inviteAcceptedEndpoint = trim(self::ENDPOINT_INVITE_ACCEPTED, "/");
        return "https://$senderHost/apps/$appName/$inviteAcceptedEndpoint";
    }

    /**
     * Returns the invite link with the specified parameters.
     * 
     * @param array the parameters to include in the link
     * @return string the invite link
     */
    public function inviteLink(array $params): string
    {
        // the forward invite endpoint
        $forwardInviteEndpoint = $this->getFullForwardInviteEndpoint();

        $parameters = '';
        foreach ($params as $key => $value) {
            $parameters .= "&$key=$value";
        }
        $parameters = trim($parameters, "&");

        $inviteLink = "$forwardInviteEndpoint?$parameters";
        return $inviteLink;
    }

    /**
     * Returns the domain of this mesh node as configured.
     * 
     * @return string the domain
     */
    public function getDomain(): string
    {
        $domain = $this->getAppValue('domain');
        return $domain;
    }

    /**
     * Returns the value of the specified application key.
     * 
     * @return mixed
     */
    public function getAppValue($key)
    {
        return $this->config->getAppValue($this->appName, $key);
    }

    /**
     * Sets the value of the specified application key.
     */
    public function setAppValue($key, $value): void
    {
        $this->config->setAppValue($this->appName, $key, $value);
    }
}
