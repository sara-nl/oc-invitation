<?php

namespace OCA\RDMesh\Federation;

use OCP\AppFramework\Db\Entity;

/**
 * Class Invitation
 * 
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
class Invitation extends Entity
{
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
}
