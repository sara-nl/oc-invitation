<?php

/**
 * This entity represents the remote user for both sides in the invitation trust relationship.
 * 
 */

namespace OCA\Invitation\Federation;

use JsonSerializable;
use OCA\Invitation\Db\Schema;
use OCP\AppFramework\Db\Entity;

/**
 * @method integer getInvitationID()
 * @method void setInvitationID(string $invitationID)
 * @method string getUserCloudID()
 * @method void setUserCloudID(string userCloudID)
 * @method string getUserName();
 * @method void setUserName(string $userName);
 * @method string getRemoteUserCloudID();
 * @method void setRemoteUserCloudID(string $remoteUserCloudID);
 * @method string getRemoteUserName()
 * @method void setRemoteUserName(string $remoteUserName)
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

    public function jsonSerialize()
    {
        return [
            'id' => $this->id,
            $this->columnToProperty(Schema::RemoteUser_invitation_id) => $this->invitationID,
            $this->columnToProperty(Schema::RemoteUser_user_cloud_id) => $this->userCloudID,
            $this->columnToProperty(Schema::RemoteUser_user_name) => $this->userName,
            $this->columnToProperty(Schema::RemoteUser_remote_user_cloud_id) => $this->remoteUserCloudID,
            $this->columnToProperty(Schema::RemoteUser_remote_user_name) => $this->remoteUserName,
        ];
    }
}
