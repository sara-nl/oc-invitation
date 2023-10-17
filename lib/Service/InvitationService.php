<?php

namespace OCA\RDMesh\Service;

use Exception;
use OCA\RDMesh\AppInfo\RDMesh;
use OCA\RDMesh\Federation\Invitation;
use OCA\RDMesh\Federation\InvitationMapper;
use OCP\ILogger;

class InvitationService
{

    private InvitationMapper $mapper;
    private ILogger $logger;

    public function __construct(InvitationMapper $mapper)
    {
        $this->mapper = $mapper;
        $this->logger = \OC::$server->getLogger();
    }

    /**
     * Returns the invitation with the specified id.
     * 
     * @param int $id
     * @return Invitation
     * @throws NotFoundException in case the invitation could not be found
     */
    public function find(int $id): Invitation
    {
        $invitation = $this->mapper->find($id);
        if (isset($invitation)) {
            return $invitation;
        }
        $this->logger->debug("Invitation with id=$id not found.", ['app' => RDMesh::APP_NAME]);
        throw new NotFoundException('Invitation not found');
    }

    /**
     * Returns the invitation with the specified token.
     * 
     * @param string $token
     * @return Invitation
     * @throws NotFoundException in case the invitation could not be found
     */
    public function findByToken(string $token): Invitation
    {
        $invitation = $this->mapper->findByToken($token);
        if (isset($invitation)) {
            return $invitation;
        }
        $this->logger->debug("Invitation with token=$token not found.", ['app' => RDMesh::APP_NAME]);
        throw new NotFoundException('Invitation not found');
    }

    /**
     * Inserts the specified invitation.
     * 
     * @param Invitation $invitation
     * @return Invitation
     * @throws ServiceException
     */
    public function insert(Invitation $invitation): Invitation
    {
        try {
            return $this->mapper->insert($invitation);
        } catch (Exception $e) {
            throw new ServiceException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Updates the invitation according to the specified fields and values.
     * 
     * @param array $fieldsAndValues
     * @return bool true if update succeeded, otherwise false
     */
    public function update(array $fieldsAndValues): bool
    {
        return $this->mapper->updateInvitation($fieldsAndValues);
    }
}
