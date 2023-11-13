<?php

namespace OCA\RDMesh\Service;

use Exception;
use OCA\RDMesh\AppInfo\RDMesh;
use OCA\RDMesh\Db\Schema;
use OCA\RDMesh\Federation\Invitation;
use OCA\RDMesh\Federation\InvitationMapper;
use OCA\RDMesh\Federation\VInvitation;
use OCP\ILogger;

/**
 * The service between controller and persistancy layer:
 *  - invitation access rights of the current user are handled here
 */
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
     * @return VInvitation
     * @throws NotFoundException in case the invitation could not be found
     */
    public function find(int $id): VInvitation
    {
        try {
            $invitation = $this->mapper->find($id);
            if (\OC::$server->getUserSession()->getUser()->getCloudId() === $invitation->getUserCloudID()) {
                return $invitation;
            }
            $this->logger->debug("User with cloud id '" . \OC::$server->getUserSession()->getUser()->getCloudId() . "' is not authorized to access invitation with id '$id'.", ['app' => RDMesh::APP_NAME]);
            throw new NotFoundException("Invitation with id=$id not found.");
        } catch (NotFoundException $e) {
            $this->logger->debug($e->getMessage() . ' Stacktrace: ' . $e->getTraceAsString(), ['app' => RDMesh::APP_NAME]);
            throw new NotFoundException("Invitation with id=$id not found.");
        } catch (Exception $e) {
            $this->logger->error($e->getMessage() . ' Stacktrace: ' . $e->getTraceAsString(), ['app' => RDMesh::APP_NAME]);
            throw new NotFoundException("Invitation with id=$id not found.");
        }
    }

    /**
     * Returns the invitation with the specified token.
     * 
     * @param string $token
     * @param bool $loginRequired true if we need session user access check, default is true
     * @return VInvitation
     * @throws NotFoundException in case the invitation could not be found
     * @throws ServiceException in case of error
     */
    public function findByToken(string $token, bool $loginRequired = true): VInvitation
    {
        $invitation = null;
        try {
            $invitation = $this->mapper->findByToken($token);
        } catch (NotFoundException $e) {
            $this->logger->error("Invitation not found for token '$token'.", ['app' => RDMesh::APP_NAME]);
            throw new NotFoundException("An exception occurred trying to retrieve the invitation with token '$token'.");
        }
        if($loginRequired == true && \OC::$server->getUserSession()->getUser() == null) {
            throw new ServiceException("Unable to find invitation, unauthenticated.");
        }
        if($loginRequired == false 
            || \OC::$server->getUserSession()->getUser()->getCloudId() === $invitation->getUserCloudID()) {
            return $invitation;
        }
        throw new NotFoundException("An exception occurred trying to retrieve the invitation with token '$token'.");
    }

    /**
     * Returns all invitations matching the specified criteria.
     * 
     * @param array $criteria
     * @param bool $loginRequired true if we need session user access check, default is true
     * @return array
     * @throws ServiceException
     */
    public function findAll(array $criteria, bool $loginRequired = true): array
    {
        try {
            // add access restriction
            if ($loginRequired) {
                array_push($criteria, [Schema::VInvitation_user_cloud_id => \OC::$server->getUserSession()->getUser()->getCloudId()]);
            }
            return $this->mapper->findAll($criteria);
        } catch (Exception $e) {
            $this->logger->error('findAll failed with error: ' . $e->getMessage() . ' Trace: ' . $e->getTraceAsString(), ['app' => RDMesh::APP_NAME]);
            throw new ServiceException('Failed to find all invitations for the specified criteria.');
        }
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
            $this->logger->error('Message: ' . $e->getMessage() . ' Stacktrace: ' . $e->getTraceAsString(), ['app' => RDMesh::APP_NAME]);
            throw new ServiceException('Error inserting the invitation.');
        }
    }

    /**
     * Updates the invitation according to the specified fields and values.
     * 
     * @param array $fieldsAndValues one of which must be the id
     * @param bool $loginRequired true if we need session user access check, default is true
     * @return bool true if update succeeded, otherwise false
     * @throws 
     */
    public function update(array $fieldsAndValues, bool $loginRequired = true): bool
    {
        if ($loginRequired === true) {
            if (\OC::$server->getUserSession()->getUser() == null) {
                $this->logger->debug('Unable to update invitation, unauthenticated.', ['app' => RDMesh::APP_NAME]);
                return false;
            }
            return $this->mapper->updateInvitation($fieldsAndValues, \OC::$server->getUserSession()->getUser()->getCloudId());
        } else {
            return $this->mapper->updateInvitation($fieldsAndValues);
        }
    }
}