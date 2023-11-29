<?php

/**
 * Represents this EFSS instance's mesh registry service.
 *
 */

namespace OCA\Invitation\Service\MeshRegistry;

use Exception;
use OCA\Invitation\AppInfo\InvitationApp;
use OCA\Invitation\Federation\DomainProvider;
use OCA\Invitation\Federation\DomainProviderMapper;
use OCA\Invitation\Service\NotFoundException;
use OCA\Invitation\Service\ServiceException;
use OCP\IConfig;
use OCP\ILogger;

class MeshRegistryService
{
    private string $appName;
    private IConfig $config;
    private DomainProviderMapper $domainProviderMapper;
    private ILogger $logger;

    // TODO: move all this to a more appropriate class
    private const ENDPOINT_FORWARD_INVITE = '/registry/forward-invite';
    public const ENDPOINT_ACCEPT_INVITE = '/accept-invite';
    public const ENDPOINT_HANDLE_INVITE = '/handle-invite';
    public const ENDPOINT_INVITE_ACCEPTED = '/ocm/invite-accepted';
    private const ROUTE_PAGE_WAYF = 'page.wayf';
    /** The domain of the sender's provider */
    public const PARAM_NAME_PROVIDER_DOMAIN = 'providerDomain';
    /** The domain of the recipient's provider */
    public const PARAM_NAME_RECIPIENT_PROVIDER = 'recipientProvider';
    public const PARAM_NAME_TOKEN = 'token';
    public const PARAM_NAME_USER_ID = 'userID';
    public const PARAM_NAME_EMAIL = 'email';
    public const PARAM_NAME_SENDER_EMAIL = 'senderEmail';
    public const PARAM_NAME_NAME = 'name';


    public function __construct($appName, IConfig $config, DomainProviderMapper $domainProviderMapper)
    {
        $this->appName = $appName;
        $this->config = $config;
        $this->domainProviderMapper = $domainProviderMapper;
        $this->logger = \OC::$server->getLogger();
    }

    /**
     * Returns the full 'https://...host.../forward-invite' endpoint of this EFSS instance
     *
     * @return string
     * @throws ServiceException
     */
    public function getFullForwardInviteEndpoint()
    {
        try {
            $domain = $this->getDomain();
            $appName = $this->appName;
            $forwardInviteEndpoint = trim(self::ENDPOINT_FORWARD_INVITE, "/");
            return "https://$domain/apps/$appName/$forwardInviteEndpoint";
        } catch (ServiceException $e) {
            $this->logger->error("getFullForwardInviteEndpoint failed with error: " . $e->getMessage() . " Trace: " . $e->getTraceAsString(), ['app' => InvitationApp::APP_NAME]);
            throw new ServiceException("Could not retrieve full 'forward invite' endpoint.");
        }
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
        try {
            $host = $this->getDomain();
            $appName = $this->appName;
            $acceptInviteEndpoint = trim(self::ENDPOINT_ACCEPT_INVITE, "/");
            return "https://$host/apps/$appName/$acceptInviteEndpoint";
        } catch (ServiceException $e) {
            $this->logger->error("getFullAcceptInviteEndpointURL failed with error: " . $e->getMessage() . " Trace: " . $e->getTraceAsString(), ['app' => InvitationApp::APP_NAME]);
            throw new ServiceException("Could not retrieve full 'accept invite' endpoint.");
        }
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
     * Returns the domain of the local (this instance's) domain provider.
     *
     * @return string the domain
     * @throws ServiceException if the domain has not been set
     */
    public function getDomain(): string
    {
        $domain = $this->getAppValue('domain');
        if (!isset($domain) || trim($domain) == "") {
            throw new ServiceException('Domain is not set.');
        }
        return $domain;
    }

    /**
     * Sets the domain of the local (this instance's) domain provider and returns the domain.
     *
     * @param string $domain
     * @return string
     * @throws ServiceException
     */
    public function setDomain(string $domain): string
    {
        if (!$this->isDomainValid($domain)) {
            throw new ServiceException("Invalid domain '$domain'");
        }

        try {
            $domainProvider = $this->getDomainProvider();
            $domainProvider->setDomain($domain);
            $domainProvider = $this->domainProviderMapper->update($domainProvider);
        } catch (NotFoundException $e) {
            $this->logger->info("A local domain provider does not exist (yet). Setting the domain to '$domain'", ['app' => InvitationApp::APP_NAME]);
        } catch (Exception $e) {
            $this->logger->error($e->getMessage() . ' Stack: ' . $e->getTraceAsString(), ['app' => InvitationApp::APP_NAME]);
            throw new ServiceException("Unable to set the domain to '$domain'.");
        }
        $this->setAppValue('domain', $domain);
        return $domain;
    }

    /**
     * Returns the domain provider of this instance.
     *
     * @return DomainProvider
     * @throws NotFoundException
     */
    public function getDomainProvider(): DomainProvider
    {
        try {
            return $this->domainProviderMapper->getDomainProvider($this->getDomain());
        } catch (ServiceException $e) {
            throw new NotFoundException($e->getMessage());
        }
    }

    /**
     * Find and returns the domain provider with the specified domain,
     * or throws a NotFoundException if it could not be found.
     *
     * @param $domain
     * @throws NotFoundException
     */
    public function findDomainProvider(string $domain): DomainProvider
    {
        return $this->domainProviderMapper->getDomainProvider($domain);
    }

    /**
     * Adds the specified domain provider and returns it, also if it exists already.
     *
     * @param $domain
     * @return DomainProvider
     * @throws ServiceException in case of error
     */
    public function addDomainProvider(string $domain): DomainProvider
    {
        if (!$this->isDomainValid($domain)) {
            throw new ServiceException("Invalid domain '$domain'");
        }
        $domainProvider = null;
        try {
            $domainProvider = $this->findDomainProvider($domain);
        } catch (NotFoundException $e) {
            $this->logger->info("Will create domain provider with domain '$domain'.", ['app' => InvitationApp::APP_NAME]);
        }
        if (isset($domainProvider)) {
            return $domainProvider;
        }
        $domainProvider = new DomainProvider();
        $domainProvider->setDomain($domain);
        try {
            return $this->domainProviderMapper->insert($domainProvider);
        } catch (Exception $e) {
            $this->logger->error('Message: ' . $e->getMessage() . ' Stacktrace: ' . $e->getTraceAsString(), ['app' => InvitationApp::APP_NAME]);
            throw new ServiceException('Error inserting the domain provider.');
        }
    }

    /**
     * Deletes the domain provider with the specified domain.
     *
     * @param $domain
     * @return DomainProvider the deleted entity
     * @throws ServiceException in case of error
     */
    public function deleteDomainProvider(string $domain): DomainProvider
    {
        try {
            $domainProvider = $this->domainProviderMapper->getDomainProvider($domain);
            return $this->domainProviderMapper->delete($domainProvider);
        } catch (NotFoundException $e) {
            $this->logger->error('Message: ' . $e->getMessage() . ' Stacktrace: ' . $e->getTraceAsString(), ['app' => InvitationApp::APP_NAME]);
            throw new ServiceException('Error deleting the domain provider: Not found.');
        } catch (Exception $e) {
            $this->logger->error('Message: ' . $e->getMessage() . ' Stacktrace: ' . $e->getTraceAsString(), ['app' => InvitationApp::APP_NAME]);
            throw new ServiceException('Error deleting the domain provider.');
        }
    }
    /**
     * Very basic validation of the specified domain.
     * Checks:
     *  - domain should be without scheme
     *  - domain should not end with '/'
     *
     * @param string $domain
     * @return bool true if the domain validates, false otherwise
     */
    private function isDomainValid(string $domain): bool
    {
        if (!isset($domain) || trim($domain) === "") {
            return false;
        }
        $url = parse_url($domain);
        if (
            $url === false
            || isset($url['scheme'])
        ) {
            return false;
        }
        // check for some accidental characters left at beginning and end
        if (strlen($domain) != strlen(trim($domain, ":/"))) {
            return false;
        }
        return true;
    }

    /**
     * Returns all domain providers of the mesh.
     *
     * @return array[DomainProvider] all domain providers
     * @throws ServiceException
     */
    public function allDomainProviders(): array
    {
        try {
            return $this->domainProviderMapper->allDomainProviders();
        } catch (NotFoundException $e) {
            $this->logger->error('Message: ' . $e->getMessage() . ' Stacktrace: ' . $e->getTraceAsString(), ['app' => InvitationApp::APP_NAME]);
            throw new ServiceException('Error retrieving all domain providers.');
        }
    }

    /**
     * Returns true if the specified domain is of a known domain provider
     *
     * @return bool
     */
    public function isKnowDomainProvider(string $domain): bool
    {
        foreach ($this->allDomainProviders() as $domainProvider) {
            if ($domainProvider->getDomain() === $domain) {
                return true;
            }
        }
        return false;
    }

    /**
     * Returns the value of the specified application key.
     *
     * @return mixed
     */
    private function getAppValue($key)
    {
        return $this->config->getAppValue($this->appName, $key);
    }

    /**
     * Sets the value of the specified application key.
     */
    private function setAppValue($key, $value): void
    {
        $this->config->setAppValue($this->appName, $key, $value);
    }
}
