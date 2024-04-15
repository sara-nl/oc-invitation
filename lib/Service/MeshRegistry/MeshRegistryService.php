<?php

/**
 * Represents this EFSS instance's mesh registry service.
 *
 */

namespace OCA\Invitation\Service\MeshRegistry;

use Exception;
use OCA\Invitation\AppInfo\InvitationApp;
use OCA\Invitation\Db\Schema;
use OCA\Invitation\Federation\InvitationServiceProvider;
use OCA\Invitation\Federation\InvitationServiceProviderMapper;
use OCA\Invitation\Service\ApplicationConfigurationException;
use OCA\Invitation\Service\InvitationService;
use OCA\Invitation\Service\NotFoundException;
use OCA\Invitation\Service\ServiceException;
use OCP\IConfig;
use OCP\ILogger;

class MeshRegistryService
{
    private string $appName;
    private IConfig $config;
    private InvitationServiceProviderMapper $invitationServiceProviderMapper;
    private ILogger $logger;

    private const ENDPOINT_FORWARD_INVITE = '/registry/forward-invite';
    public const ENDPOINT_GET_INVITE = '/invite';
    public const ENDPOINT_ACCEPT_INVITE = '/accept-invite';
    public const ENDPOINT_HANDLE_INVITE = '/handle-invite';
    public const ENDPOINT_INVITE_ACCEPTED = '/ocm/invite-accepted';
    public const ENDPOINT_INVITATION_SERVICE_PROVIDER = '/registry/invitation-service-provider';
    private const ROUTE_PAGE_WAYF = 'page.wayf';
    /** @depricated The domain of the sender's provider */
    public const PARAM_NAME_PROVIDER_DOMAIN = 'providerDomain';
    /** The endpoint of the sender's provider */
    public const PARAM_NAME_PROVIDER_ENDPOINT = 'providerEndpoint';
    /** OCM param recipientProvider */
    public const PARAM_NAME_RECIPIENT_PROVIDER = 'recipientProvider';
    /** @depricated The domain of the recipient's provider */
    public const PARAM_NAME_RECIPIENT_DOMAIN = 'recipientDomain';
    /** The endpoint of the recipient's provider */
    public const PARAM_NAME_RECIPIENT_ENDPOINT = 'recipientEndpoint';
    public const PARAM_NAME_TOKEN = 'token';
    public const PARAM_NAME_USER_ID = 'userID';
    public const PARAM_NAME_EMAIL = 'email';
    public const PARAM_NAME_SENDER_EMAIL = 'senderEmail';
    public const PARAM_NAME_NAME = 'name';


    public function __construct($appName, IConfig $config, InvitationServiceProviderMapper $invitationServiceProviderMapper)
    {
        $this->appName = $appName;
        $this->config = $config;
        $this->invitationServiceProviderMapper = $invitationServiceProviderMapper;
        $this->logger = \OC::$server->getLogger();
    }

    /**
     * Returns the full 'https://{invitation_service_provider}/forward-invite' endpoint of this EFSS instance
     *
     * @return string
     * @throws ServiceException
     */
    public function getFullForwardInviteEndpoint()
    {
        try {
            $invitationServiceEndpoint = $this->getEndpoint();
            $forwardInviteEndpoint = trim(self::ENDPOINT_FORWARD_INVITE, "/");
            return "$invitationServiceEndpoint/$forwardInviteEndpoint";
        } catch (ServiceException $e) {
            $this->logger->error("getFullForwardInviteEndpoint failed with error: " . $e->getMessage() . " Trace: " . $e->getTraceAsString(), ['app' => InvitationApp::APP_NAME]);
            throw new ServiceException("Could not retrieve full '/forward-invite' endpoint.");
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
     * Returns the full 'https://{invitation_service_provider}/accept-invite' endpoint URL of this EFSS instance.
     *
     * @return string the full /accept-invite endpoint URL
     */
    public function getFullAcceptInviteEndpointURL(): string
    {
        try {
            $invitationServiceEndpoint = $this->getEndpoint();
            $acceptInviteEndpoint = trim(self::ENDPOINT_ACCEPT_INVITE, "/");
            return "$invitationServiceEndpoint/$acceptInviteEndpoint";
        } catch (ServiceException $e) {
            $this->logger->error("getFullAcceptInviteEndpointURL failed with error: " . $e->getMessage() . " Trace: " . $e->getTraceAsString(), ['app' => InvitationApp::APP_NAME]);
            throw new ServiceException("Could not retrieve full '/accept-invite' endpoint.");
        }
    }

    /**
     * Returns the full 'https://{invitation_service_provider}/invite-accepted' endpoint URL of this EFSS instance.
     *
     * @param string $senderHost the host of the sender of the invitation
     * @return string the full /invite-accepted endpoint URL
     */
    public function getFullInviteAcceptedEndpointURL(string $senderInvitationServiceProviderEndpoint = ""): string
    {
        if ($senderInvitationServiceProviderEndpoint == "") {
            return ['error' => "unable to build full '/invite-accepted' endpoint URL, sender invitation service provider endpoint not specified"];
        }
        $endpoint = trim(trim($senderInvitationServiceProviderEndpoint), '/');
        $inviteAcceptedEndpoint = trim(trim(self::ENDPOINT_INVITE_ACCEPTED), "/");
        return "$endpoint/$inviteAcceptedEndpoint";
    }

    /**
     * Returns the full invitation service provider url based on the specified host endpoint.
     *
     * @param string $endpoint
     * @return string
     */
    public function getFullInvitationServiceProviderEndpointUrl(string $endpoint): string
    {
        $ep = trim(trim($endpoint), '/');
        $ispEndpoint = trim(trim(self::ENDPOINT_INVITATION_SERVICE_PROVIDER), '/');
        return "$ep/$ispEndpoint";
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
     * Returns the endpoint of the local (this instance's) invitation service provider.
     *
     * @return string the endpoint
     * @throws ApplicationConfigurationException if the endpoint has not been set
     */
    public function getEndpoint(): string
    {
        $endpoint = $this->getAppValue('endpoint');
        if (!isset($endpoint) || trim($endpoint) == "") {
            throw new ApplicationConfigurationException('Endpoint is not set.');
        }
        return $endpoint;
    }

    /**
     * Sets the endpoint of the local (this instance's) invitation service provider and returns the endpoint.
     *
     * @param string $endpoint
     * @return string
     * @throws ServiceException
     */
    public function setEndpoint(string $endpoint): string
    {
        if (!$this->isEndpointValid($endpoint)) {
            throw new ServiceException("Invalid endpoint '$endpoint'");
        }

        try {
            $invitationServiceProvider = $this->getInvitationServiceProvider();
            $invitationServiceProvider->setEndpoint($endpoint);
            $invitationServiceProvider = $this->invitationServiceProviderMapper->update($invitationServiceProvider);
        } catch (NotFoundException $e) {
            $this->logger->info("A local invitation service provider does not exist (yet). Setting the endpoint to '$endpoint'", ['app' => InvitationApp::APP_NAME]);
        } catch (Exception $e) {
            $this->logger->error($e->getMessage() . ' Stack: ' . $e->getTraceAsString(), ['app' => InvitationApp::APP_NAME]);
            throw new ServiceException("Unable to set the endpoint to '$endpoint'.");
        }
        $this->setAppValue('endpoint', $endpoint);
        return $endpoint;
    }

    /**
     * Returns the invitation service provider of this instance.
     *
     * @return InvitationServiceProvider
     * @throws ApplicationConfigurationException
     * @throws NotFoundException
     */
    public function getInvitationServiceProvider(): InvitationServiceProvider
    {
        try {
            return $this->invitationServiceProviderMapper->getInvitationServiceProvider($this->getEndpoint());
        } catch (ApplicationConfigurationException $e) {
            throw $e;
        } catch (ServiceException $e) {
            throw new NotFoundException($e->getMessage());
        }
    }

    /**
     * Find and returns the invitation service provider with the specified endpoint,
     * or throws a NotFoundException if it could not be found.
     *
     * @param $endpoint
     * @throws NotFoundException
     */
    public function findInvitationServiceProvider(string $endpoint): InvitationServiceProvider
    {
        return $this->invitationServiceProviderMapper->getInvitationServiceProvider($endpoint);
    }

    /**
     * Adds the specified invitation service provider and returns it, also if it exists already.
     *
     * @param InvitationServiceProvider $provider
     * @return InvitationServiceProvider
     * @throws ServiceException in case of error
     */
    public function addInvitationServiceProvider(InvitationServiceProvider $provider): InvitationServiceProvider
    {
        $invitationServiceProvider = null;
        try {
            $invitationServiceProvider = $this->findInvitationServiceProvider($provider->getEndpoint());
        } catch (NotFoundException $e) {
            $this->logger->info("Creating invitation service provider with endpoint " . $provider->getEndpoint(), ['app' => InvitationApp::APP_NAME]);
        }
        if (isset($invitationServiceProvider)) {
            return $invitationServiceProvider;
        }
        try {
            return $this->invitationServiceProviderMapper->insert($provider);
        } catch (Exception $e) {
            $this->logger->error('Message: ' . $e->getMessage() . ' Trace: ' . $e->getTraceAsString(), ['app' => InvitationApp::APP_NAME]);
            throw new ServiceException('Error inserting the invitation service provider.');
        }
    }

    /**
     * Updates the fields of invitation service provider with the specified endpoint or
     * registers the provider if it is not registered yet.
     *
     * @param string endpoint the currently configured provider endpoint
     * @param array $fields updates on current provider fields
     * @return InvitationServiceProvider the updated provider
     * @throws ServiceException
     */
    public function updateInvitationServiceProvider($endpoint, $fields): InvitationServiceProvider
    {
        try {
            $this->logger->debug(" updating: $endpoint with fields: " . print_r($fields, true));
            $invitationServiceProvider = null;
            $newEndpoint = "";
            $newName = "";
            try {
                $invitationServiceProvider = $this->invitationServiceProviderMapper->getInvitationServiceProvider($endpoint);
            } catch (NotFoundException $e) {
                // create it first, unless $endpoint is empty in which case we expect it to be in the fields
                if (!empty($endpoint)) {
                    if (!$this->isEndpointValid($endpoint)) {
                        throw new ServiceException("Error updating invitation service provider. Endpoint invalid: $endpoint");
                    }
                    $invitationServiceProvider = new InvitationServiceProvider();
                    $invitationServiceProvider->setEndpoint($endpoint);
                    $invitationServiceProvider = $this->invitationServiceProviderMapper->insert($invitationServiceProvider);
                    $newEndpoint = $endpoint;
                } else {
                    $this->logger->info("Registering this Invitation Service Provider to the registry.");
                }
            }
            foreach ($fields as $field => $value) {
                switch ($field) {
                    case Schema::INVITATION_SERVICE_PROVIDER_ENDPOINT:
                        if (is_string($value) == true) {
                            if (!$this->isEndpointValid($value)) {
                                throw new ServiceException("Error updating invitation service provider. Endpoint invalid: $value");
                            }
                            $newEndpoint = $value;
                        } else {
                            $this->logger->debug("Value '$value' is of wrong type for property endpoint");
                        }
                        break;
                    case Schema::INVITATION_SERVICE_PROVIDER_NAME:
                        if (is_string($value) == true) {
                            $newName = $value;
                        } else {
                            $this->logger->debug("Value '$value' is of wrong type for property name");
                        }
                        break;
                    default:
                        $this->logger->debug("Field '$field' is not a property of entity InvitationServiceProvider");
                }
            }
            $domain = parse_url($newEndpoint)['host'];
            if ($invitationServiceProvider == null) {
                $invitationServiceProvider = new InvitationServiceProvider();
                $invitationServiceProvider->setEndpoint($newEndpoint);
                $invitationServiceProvider->setName($newName);
                $invitationServiceProvider->setDomain($domain);
                $invitationServiceProvider = $this->invitationServiceProviderMapper->insert($invitationServiceProvider);
            } else {
                $invitationServiceProvider->setEndpoint($newEndpoint);
                $invitationServiceProvider->setName($newName);
                $invitationServiceProvider->setDomain($domain);
                $invitationServiceProvider = $this->invitationServiceProviderMapper->update($invitationServiceProvider);
            }
            $this->setAppValue('endpoint', $newEndpoint);
            return $invitationServiceProvider;
        } catch (Exception $e) {
            $this->logger->error('Message: ' . $e->getMessage() . ' Trace: ' . $e->getTraceAsString(), ['app' => InvitationApp::APP_NAME]);
            throw new ServiceException("Error updating invitation service provider with endpoint '$endpoint'");
        }
    }

    /**
     * Deletes the invitation service provider with the specified endpoint.
     *
     * @param $endpoint
     * @return InvitationServiceProvider the deleted entity
     * @throws ServiceException in case of error
     */
    public function deleteInvitationServiceProvider(string $endpoint): InvitationServiceProvider
    {
        try {
            $invitationServiceProvider = $this->invitationServiceProviderMapper->getInvitationServiceProvider($endpoint);
            $deletedEntity = $this->invitationServiceProviderMapper->delete($invitationServiceProvider);
            if ($endpoint === $this->getEndpoint()) {
                $this->deleteAppValue('endpoint');
            }
            return $deletedEntity;
        } catch (NotFoundException $e) {
            $this->logger->error('Message: ' . $e->getMessage() . ' Trace: ' . $e->getTraceAsString(), ['app' => InvitationApp::APP_NAME]);
            throw new ServiceException('Error deleting the invitation service provider: Not found.');
        } catch (Exception $e) {
            $this->logger->error('Message: ' . $e->getMessage() . ' Trace: ' . $e->getTraceAsString(), ['app' => InvitationApp::APP_NAME]);
            throw new ServiceException('Error deleting the invitation service provider.');
        }
    }
    /**
     * Very basic validation of the specified endpoint.
     * Checks:
     *  - endpoint should be with https scheme
     *  - endpoint should not end with '/'
     *
     * @param string $endpoint
     * @return bool true if the endpoint validates, false otherwise
     */
    private function isEndpointValid(string $endpoint): bool
    {
        if (!isset($endpoint) || trim($endpoint) === "") {
            return false;
        }
        $url = parse_url($endpoint);
        if (
            $url === false
            || !isset($url['scheme'])
            || $url['scheme'] != 'https'
        ) {
            return false;
        }
        // check for some accidental characters left at beginning and end
        if (strlen($endpoint) != strlen(trim($endpoint, ":/"))) {
            return false;
        }
        return true;
    }

    /**
     * Returns all invitation service providers of the mesh.
     *
     * @return array[InvitationServiceProvider] all invitation service providers
     * @throws ServiceException
     */
    public function allInvitationServiceProviders(): array
    {
        try {
            return $this->invitationServiceProviderMapper->allInvitationServiceProviders();
        } catch (NotFoundException $e) {
            $this->logger->error('Message: ' . $e->getMessage() . ' Trace: ' . $e->getTraceAsString(), ['app' => InvitationApp::APP_NAME]);
            throw new ServiceException('Error retrieving all invitation service providers.');
        }
    }

    /**
     * Returns true if the specified endpoint is of a known invitation service provider
     *
     * @return bool
     */
    public function isKnowInvitationServiceProvider(string $endpoint): bool
    {
        foreach ($this->allInvitationServiceProviders() as $invitationServiceProvider) {
            if ($invitationServiceProvider->getEndpoint() === $endpoint) {
                return true;
            }
        }
        return false;
    }

    /**
     * Set the name associated with this invitation service provider instance.
     *
     * @param string $name
     * @return string the new name
     * @throws ServiceException
     */
    public function setName(string $name): string
    {
        try {
            $invitationServiceProvider = $this->getInvitationServiceProvider();
            $invitationServiceProvider->setName($name);
            $invitationServiceProvider = $this->invitationServiceProviderMapper->update($invitationServiceProvider);
            return $invitationServiceProvider->getName();
        } catch (NotFoundException $e) {
            $this->logger->error('Message: ' . $e->getMessage() . ' Trace: ' . $e->getTraceAsString(), ['app' => InvitationApp::APP_NAME]);
            throw new ServiceException("Error updating invitation service provider: Not found");
        } catch (Exception $e) {
            $this->logger->error($e->getMessage() . ' Stack: ' . $e->getTraceAsString(), ['app' => InvitationApp::APP_NAME]);
            throw new ServiceException("Could not set name to '$name'.");
        }
    }

    /**
     * Get the name associated with this invitation service provider instance.
     *
     * @return string the name
     * @throws ServiceException
     */
    public function getName(): string
    {
        $invitationServiceProvider = $this->getInvitationServiceProvider();
        return $invitationServiceProvider->getName();
    }

    /**
     * Set whether it is allowed to share with invited users only.
     *
     * @param bool $allowSharingWithInvitedUsersOnly
     * @return bool the new value
     */
    public function setAllowSharingWithInvitedUsersOnly(bool $allow): bool
    {
        $this->setAppValue(InvitationApp::CONFIG_ALLOW_SHARING_WITH_INVITED_USERS_ONLY, $allow ? 'yes' : 'no');
        return $this->getAppValue(InvitationApp::CONFIG_ALLOW_SHARING_WITH_INVITED_USERS_ONLY) === 'yes';
    }

    /**
     * Returns whether it is allowed to share with invited users only.
     *
     * @return bool
     */
    public function getAllowSharingWithInvitedUsersOnly(): bool
    {
        return strtolower($this->getAppValue(InvitationApp::CONFIG_ALLOW_SHARING_WITH_INVITED_USERS_ONLY)) === 'yes';
    }

    /**
     * Returns the value of the specified application key.
     *
     * @param string $key
     * @return mixed
     */
    private function getAppValue($key)
    {
        return $this->config->getAppValue($this->appName, $key);
    }

    /**
     * Deletes the app config set for the specified key.
     *
     * @param string $key
     * @return mixed
     */
    private function deleteAppValue($key)
    {
        return $this->config->deleteAppValue($this->appName, $key);
    }

    /**
     * Sets the value of the specified application key.

     * @param string $key
     * @param string $value
     * @return void
     */
    private function setAppValue($key, $value): void
    {
        $this->config->setAppValue($this->appName, $key, $value);
    }
}
