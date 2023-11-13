<?php

/**
 * This class represents the Invitation view entity.
 * 
 */

namespace OCA\Invitation\Federation;

use JsonSerializable;
use OCA\Invitation\Db\Schema;
use OCP\AppFramework\Db\Entity;

/**
 * @method string getToken()
 * @method void setToken(string $token)
 * @method string getSentReceived()
 * @method void setSentReceived(string sentReceived)
 * @method string getProviderDomain()
 * @method void setProviderDomain(string $providerDomain)
 * @method string getRecipientDomain()
 * @method void setRecipientDomain(string $recipientDomain)
 * @method string getSenderCloudId()
 * @method void setSenderCloudId(string $senderCloudId)
 * @method string getSenderEmail()
 * @method void setSenderEmail(string $senderEmail)
 * @method string getSenderName()
 * @method void setSenderName(string $senderName)
 * @method string getRecipientCloudId()
 * @method void setRecipientCloudId(string $recipientCloudId)
 * @method string getRecipientEmail()
 * @method void setRecipientEmail(string $recipientEmail)
 * @method string getRecipientName()
 * @method void setRecipientName(string $recipientName)
 * @method string getUserCloudID()
 * @method void setUserCloudID(string userCloudID)
 * @method string getRemoteUserName()
 * @method void setRemoteUserName(string remoteUserName)
 * @method string getRemoteCloudID()
 * @method void setRemoteUserCloudID(string remoteUserCloudID)
 * @method string getRemoteUserEmail()
 * @method void setRemoteUserEmail(string remoteUserEmail)
 * @method int getTimestamp()
 * @method void setTimestamp(int $timestamp)
 * @method string getStatus()
 * @method void setStatus(string $status)
 */
class VInvitation extends Entity implements JsonSerializable
{
    protected $token;
    protected $timestamp;
    protected $status;
    protected $userCloudID;
    protected $sentReceived;
    protected $providerDomain;
    protected $recipientDomain;
    protected $senderCloudId;
    protected $senderEmail;
    protected $senderName;
    protected $recipientCloudId;
    protected $recipientEmail;
    protected $recipientName;
    protected $remoteUserCloudID;
    protected $remoteUserName;
    protected $remoteUserEmail;

    public function jsonSerialize()
    {
        return [
            'id' => $this->id,
            $this->columnToProperty(Schema::VInvitation_token) => $this->token,
            $this->columnToProperty(Schema::VInvitation_timestamp) => $this->timestamp,
            $this->columnToProperty(Schema::VInvitation_status) => $this->status,
            $this->columnToProperty(Schema::VInvitation_user_cloud_id) => $this->userCloudID,
            $this->columnToProperty(Schema::VInvitation_sent_received) => $this->sentReceived,
            $this->columnToProperty(Schema::VInvitation_provider_domain) => $this->providerDomain,
            $this->columnToProperty(Schema::VInvitation_recipient_domain) => $this->recipientDomain,
            $this->columnToProperty(Schema::VInvitation_sender_cloud_id) => $this->senderCloudId,
            $this->columnToProperty(Schema::VInvitation_sender_name) => $this->senderName,
            $this->columnToProperty(Schema::VInvitation_sender_email) => $this->senderEmail,
            $this->columnToProperty(Schema::VInvitation_recipient_cloud_id) => $this->recipientCloudId,
            $this->columnToProperty(Schema::VInvitation_recipient_email) => $this->recipientEmail,
            $this->columnToProperty(Schema::VInvitation_recipient_name) => $this->recipientName,
            $this->columnToProperty(Schema::VInvitation_remote_user_name) => $this->remoteUserName,
            $this->columnToProperty(Schema::VInvitation_remote_user_cloud_id) => $this->remoteUserCloudID,
            $this->columnToProperty(Schema::VInvitation_remote_user_email) => $this->remoteUserEmail
        ];
    }
}
