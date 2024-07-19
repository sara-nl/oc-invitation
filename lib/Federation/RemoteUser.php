<?php

/**
 * This entity represents the remote user for both sides in the invitation trust relationship.
 *
 */

namespace OCA\Collaboration\Federation;

use JsonSerializable;
use OCA\Collaboration\Db\Schema;
use OCP\AppFramework\Db\Entity;

/**
 * @method integer getInvitationID()
 * @method void setInvitationID(string $invitationID)
 * @method string getUserCloudID()
 * @method void setUserCloudID(string $userCloudID)
 * @method string getUserName()
 * @method void setUserName(string $userName)
 * @method string getRemoteUserCloudID()
 * @method void setRemoteUserCloudID(string $remoteUserCloudID)
 * @method string getRemoteUserName()
 * @method void setRemoteUserName(string $remoteUserName)
 * @method string getRemoteUserEmail()
 * @method void setRemoteUserEmail(string $remoteUserEmail)
 * @method string getRemoteUserProviderEndpoint()
 * @method void setRemoteUserProviderEndpoint(string $remoteUserProviderEndpoint)
 * @method string getRemoteUserProviderName()
 * @method void setRemoteUserProviderName(string $remoteUserProviderName)
 */
class RemoteUser extends Entity implements JsonSerializable
{
    /** the corresponding invitation id */
    protected $invitationID;
    /** the local user cloud id */
    protected $userCloudID;
    /** the local user name */
    protected $userName;
    /** the remote user cloud id */
    protected $remoteUserCloudID;
    /** the remote user name */
    protected $remoteUserName;
    /** the remote user email */
    protected $remoteUserEmail;
    /** the remote user provider endpoint */
    protected $remoteUserProviderEndpoint;
    /** the remote user provider name */
    protected $remoteUserProviderName;

    public function jsonSerialize()
    {
        return [
            $this->columnToProperty(Schema::REMOTEUSER_INVITATION_ID) => $this->invitationID,
            $this->columnToProperty(Schema::REMOTEUSER_USER_CLOUD_ID) => $this->userCloudID,
            $this->columnToProperty(Schema::REMOTEUSER_USER_NAME) => $this->userName,
            $this->columnToProperty(Schema::REMOTEUSER_REMOTE_USER_CLOUD_ID) => $this->remoteUserCloudID,
            $this->columnToProperty(Schema::REMOTEUSER_REMOTE_USER_NAME) => $this->remoteUserName,
            $this->columnToProperty(Schema::REMOTEUSER_REMOTE_USER_EMAIL) => $this->remoteUserEmail,
            $this->columnToProperty(Schema::REMOTEUSER_REMOTE_USER_PROVIDER_ENDPOINT) => $this->remoteUserProviderEndpoint,
            $this->columnToProperty(Schema::REMOTEUSER_REMOTE_USER_PROVIDER_NAME) => $this->remoteUserProviderName,
        ];
    }
}
