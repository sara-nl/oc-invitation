<?php

namespace OCA\RDMesh\Service;

use Exception;
use OCA\RDMesh\AppInfo\RDMesh;
use OCA\RDMesh\Db\Schema;
use OCA\RDMesh\Federation\Invitation;
use OCA\RDMesh\Federation\InvitationMapper;
use OCA\RDMesh\Federation\RemoteUserMapper;
use OCA\RDMesh\Federation\VInvitation;
use OCP\ILogger;
use OCP\Share\IRemoteShareesSearch;

/**
 * The service between controller and persistancy layer:
 *  - invitation access rights of the current user are handled here
 */
class InvitationService implements IRemoteShareesSearch
{

    private InvitationMapper $mapper;
    private RemoteUserMapper $remoteUserMapper;
    private ILogger $logger;

    public function __construct(InvitationMapper $mapper, RemoteUserMapper $remoteUserMapper)
    {
        $this->mapper = $mapper;
        $this->remoteUserMapper = $remoteUserMapper;
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
            $this->logger->debug($e->getMessage() . ' Stacktrace: ' . $e->getTraceAsString());
            throw new NotFoundException("Invitation with id=$id not found.");
        } catch (Exception $e) {
            $this->logger->error($e->getMessage() . ' Stacktrace: ' . $e->getTraceAsString());
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
        try {
            $invitation = $this->mapper->findByToken($token);
            if (isset($invitation)) {
                if ($loginRequired == false || \OC::$server->getUserSession()->getUser()->getCloudId() === $invitation->getUserCloudID()) {
                    return $invitation;
                }
                $this->logger->debug("User with cloud id '" . \OC::$server->getUserSession()->getUser()->getCloudId() . "' is not authorized to access invitation with token '$token'.", ['app' => RDMesh::APP_NAME]);
            }
        } catch (NotFoundException $e) {
            $this->logger->error("Invitation not found for token '$token'.", ['app' => RDMesh::APP_NAME]);
            throw new NotFoundException("An exception occurred trying to retrieve the invitation with token '$token'.");
        } catch (Exception $e) {
            $this->logger->error($e->getMessage() . ' Stacktrace: ' . $e->getTraceAsString(), ['app' => RDMesh::APP_NAME]);
            throw new ServiceException("An exception occurred trying to retrieve the invitation with token '$token'.");
        }
        throw new NotFoundException("Invitation with token '$token' not found.");
    }

    /**
     * Returns all invitations matching the specified criteria.
     * 
     * @param array $criteria
     * @param bool $loginRequired true if we need session user access check, default is true
     * @return array
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
            throw new ServiceException('Unable to find all invitations for the specified criteria.');
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
            $this->logger->error('Message: ' . $e->getMessage() . ' Stacktrace: ' . $e->getTraceAsString());
            throw new ServiceException('Error inserting the invitation.');
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

    /**
     * Return the identifier of this provider.
     * @param string search string for autocomplete
     * @return array[] this function should return an array
     * where each element is an associative array, containing:
     * - label: a string to display as label
     * - value: an associative array containing:
     *   - shareType: int, to be used as share type
     *   - shareWith: string, identifying the sharee
     *   - server (optional): string, URL of the server, e.g.
     * https://github.com/owncloud/core/blob/v10.12.0-beta.1/apps/files_sharing/lib/Controller/ShareesController.php#L421
     *
     * @since 10.12.0
     */
    // FIXME: move this to RemoteUserService
    public function search($search): array
    {
        try {
            // FIXME: allow returning a single not invited remote user to be compatible with the default OC behaviour
            //        this option should be configurable with a 'allow sharing with non invited users' setting
            return $this->remoteUserMapper->search($search);
        } catch (Exception $e) {
            $this->logger->error('Message: ' . $e->getMessage() . ' Stacktrace: ' . $e->getTraceAsString());
            throw new ServiceException('Error searching for remote users.');
        }
    }
}
