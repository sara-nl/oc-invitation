<?php
/**
 * This class represents the Invitation entity.
 * 
 */

namespace OCA\Invitation\Federation;

use JsonSerializable;
use OCA\Invitation\Db\Schema;
use OCP\AppFramework\Db\Entity;

/**
 * @method string getUserCloudId()
 * @method void setUserCloudId(string $userCloudId)
 * @method string getToken()
 * @method void setToken(string $token)
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
 * @method int getTimestamp()
 * @method void setTimestamp(int $timestamp)
 * @method string getStatus()
 * @method void setStatus(string $status)
 */
class Invitation extends Entity implements JsonSerializable
{
    protected $userCloudId;
    protected $token;
    protected $providerDomain;
    protected $recipientDomain;
    protected $senderCloudId;
    protected $senderEmail;
    protected $senderName;
    protected $recipientCloudId;
    protected $recipientEmail;
    protected $recipientName;
    protected $timestamp;
    protected $status;

    public const STATUS_NEW = 'new';
    public const STATUS_OPEN = 'open';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_DECLINED = 'declined';
    public const STATUS_INVALID = 'invalid';

    public function jsonSerialize()
    {
        return [
            'id' => $this->id,
            $this->columnToProperty(Schema::Invitation_user_cloud_id) => $this->userCloudId,
            $this->columnToProperty(Schema::Invitation_token) => $this->token,
            $this->columnToProperty(Schema::Invitation_provider_domain) => $this->providerDomain,
            $this->columnToProperty(Schema::Invitation_recipient_domain) => $this->recipientDomain,
            $this->columnToProperty(Schema::Invitation_sender_cloud_id) => $this->senderCloudId,
            $this->columnToProperty(Schema::Invitation_sender_email) => $this->senderEmail,
            $this->columnToProperty(Schema::Invitation_sender_name) => $this->senderName,
            $this->columnToProperty(Schema::Invitation_recipient_cloud_id) => $this->recipientCloudId,
            $this->columnToProperty(Schema::Invitation_recipient_email) => $this->recipientEmail,
            $this->columnToProperty(Schema::Invitation_recipient_name) => $this->recipientName,
            $this->columnToProperty(Schema::Invitation_timestamp) => $this->timestamp,
            $this->columnToProperty(Schema::Invitation_status) => $this->status,
        ];
    }
}
