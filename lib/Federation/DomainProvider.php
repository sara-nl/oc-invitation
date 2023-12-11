<?php

/**
 * Represents a domain provider in the mesh. Domain providers are registered in the mesh registry.
 * 
 */

// FIXME: DomainProvider seems wrong; what about InvitationServiceProvider
// Properties: 
//     invitationEndpoint  - the root of all routes
//     domain              - still usefull as the provider's key, eg. for the invitation views
//     ... OCM invite-accepted endpoint defined here as well ? as serviceable property ? 

namespace OCA\Invitation\Federation;

use JsonSerializable;
use OCA\Invitation\Db\Schema;
use OCP\AppFramework\Db\Entity;

/**
 * @method string getDomain()
 * @method setDomain(string @domain)
 */
class DomainProvider extends Entity implements JsonSerializable
{
    /** The domain that is provided */
    protected $domain;

    public function jsonSerialize()
    {
        return [
            'id' => $this->id,
            $this->columnToProperty(Schema::DOMAINPROVIDER_DOMAIN) => $this->domain,
        ];
    }
}
