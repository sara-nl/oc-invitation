<?php

/**
 * Represents a domain provider in the mesh. Domain providers are registered in the mesh registry.
 */

namespace OCA\RDMesh\Federation;

use JsonSerializable;
use OCA\RDMesh\Db\Schema;
use OCP\AppFramework\Db\Entity;

/**
 * @method string getDomain()
 * @method setDomain(string @domain)
 */
class DomainProvider extends Entity implements JsonSerializable
{
    protected $domain;

    public function jsonSerialize()
    {
        return [
            'id' => $this->id,
            $this->columnToProperty(Schema::DomainProvider_domain) => $this->domain,
        ];
    }
}
