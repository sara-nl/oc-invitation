<?php

namespace OCA\RDMesh\Service;

use OCA\RDMesh\Federation\Invitation;
use OCA\RDMesh\Federation\InvitationMapper;

class InvitationService
{

    private InvitationMapper $mapper;

    public function __construct(InvitationMapper $mapper)
    {
        $this->mapper = $mapper;
    }

    public function insert(Invitation $invitation): Invitation
    {
        return $this->mapper->insert($invitation);
    }
}
