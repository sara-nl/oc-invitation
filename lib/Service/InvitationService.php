<?php

namespace OCA\RDMesh\Service;

use Exception;
use OCA\RDMesh\AppInfo\RDMesh;
use OCA\RDMesh\Federation\Invitation;
use OCA\RDMesh\Federation\InvitationMapper;
use OCP\ILogger;
use OCP\Share;
use OCP\Share\IRemoteShareesSearch;

class InvitationService implements IRemoteShareesSearch
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
     * @throws ServiceException in case the invitation could not be returned
     */
    public function findByToken(string $token): Invitation
    {
        try {
            $invitation = $this->mapper->findByToken($token);
            if (isset($invitation)) {
                return $invitation;
            }
        } catch (Exception $e) {
            $this->logger->error("An exception occurred trying to retrieve the Invitation with token '$token'. Error message: " . $e->getMessage(), ['app' => RDMesh::APP_NAME]);
            throw new ServiceException('Invitation not found');
        }
    }

    /**
     * Returns all invitations matching the specified criteria.
     * 
     * @param array $criteria
     * @return array
     */
    public function findAll(array $criteria): array
    {
        try {
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
    public function search($search): array
    {
        $result = [];
        // TODO: implement the actual search
        array_push($result, [
            'label' => 'Sjonnie (domain: rd-2.nl)',         // TODO: display at least the invitation name + eg. the recipient domain
            'value' => [
                'shareType' => Share::SHARE_TYPE_REMOTE,    // this is a federated share by definition
                'shareWith' => 'sjonnie@rd-2.nl',           // the cloud ID
            ]
        ]);
        return $result;
    }
}
