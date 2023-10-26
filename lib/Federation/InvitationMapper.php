<?php

namespace OCA\RDMesh\Federation;

use Exception;
use OCA\RDMesh\AppInfo\RDMesh;
use OCA\RDMesh\Db\Schema;
use OCA\RDMesh\Federation\Invitation;
use OCA\RDMesh\Service\NotFoundException;
use OCP\AppFramework\Db\Mapper;
use OCP\IDb;
use OCP\ILogger;

class InvitationMapper extends Mapper
{
    private ILogger $logger;

    public function __construct(IDb $dbConnection)
    {
        parent::__construct($dbConnection, Schema::Table_Invitations, Invitation::class);
        $this->logger = \OC::$server->getLogger();
    }

    /**
     * Returns the invitation with the specified id, or null if it could not be found.

     * @param int $id
     * @return mixed
     */
    public function find(int $id)
    {
        $qb = $this->db->getQueryBuilder();
        $result = $qb->select('*')
            ->from(Schema::Table_Invitations, 'i')
            ->where($qb->expr()->eq('i.id', $qb->createNamedParameter($id)))
            ->execute()->fetchAssociative();
        if (is_array($result)) {
            return $this->newInvitation($result);
        }
        return null;
    }

    /** Returns the invitation with the specified token, or NotFoundException if it could not be found.
     * 
     * @param string $token
     * @return mixed
     * @throws NotFoundException
     */
    public function findByToken(string $token)
    {
        try {
            $qb = $this->db->getQueryBuilder();
            $result = $qb->select('*')
                ->from(Schema::Table_Invitations, 'i')
                ->where($qb->expr()->eq('i.token', $qb->createNamedParameter($token)))
                ->execute()->fetchAssociative();
            if (is_array($result) && count($result) > 0) {
                return $this->newInvitation($result);
            }
            throw new Exception("Invitation not found for token $token");
        } catch (Exception $e) {
            $this->logger->error($e->getMessage() . ' Stacktrace: ' . $e->getTraceAsString(), ['app' => RDMesh::APP_NAME]);
            throw new NotFoundException($e->getMessage());
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
        $qb = $this->db->getQueryBuilder();
        $query = $qb->select('*')->from(Schema::Table_Invitations, 'i');
        $i = 0;
        foreach ($criteria as $field => $value) {
            if ($i == 0) {
                $query->where($qb->expr()->eq("i.$field", $qb->createNamedParameter($value)));
            } else {
                $query->andWhere($qb->expr()->eq("i.$field", $qb->createNamedParameter($value)));
            }
            ++$i;
        }
        return $this->newInvitations($query->execute()->fetchAllAssociative());
    }

    /**
     * Updates the invitation according to the specified fields and values.
     * The id of the invitation must be specified as one of the fields and values.
     * 
     * @param array $fieldsAndValues
     * @return bool true if an invitation has been updated, false otherwise
     */
    public function updateInvitation(array $fieldsAndValues): bool
    {
        try {
            $qb = $this->db->getQueryBuilder();
            $updateQuery = $qb->update(Schema::Table_Invitations, 'i');
            if (isset($fieldsAndValues['id']) && count($fieldsAndValues) > 1) {
                foreach ($fieldsAndValues as $field => $value) {
                    if ($field != 'id') {
                        $updateQuery->set("i.$field", $qb->createNamedParameter($value));
                    }
                }
                $updateQuery->where(
                    $updateQuery->expr()->eq('i.id', $updateQuery->createNamedParameter($fieldsAndValues['id']))
                );
                $result = $updateQuery->execute();
                if ($result === 1) {
                    return true;
                }
            }
        } catch (Exception $e) {
            $this->logger->error('updateInvitation failed with error: ' . $e->getMessage() . ' Trace: ' . $e->getTraceAsString(), ['app' => RDMesh::APP_NAME]);
        }
        return false;
    }

    /**
     * Builds and returns a new invitation from specified the associative array.
     * @param array $associativeArray
     * @return Invitation
     */
    private function newInvitation(array $associativeArray): Invitation
    {
        if (isset($associativeArray) && count($associativeArray) > 0) {
            $invitation = new Invitation();
            $invitation->setId($associativeArray['id']);
            $invitation->setToken($associativeArray[Schema::Invitation_token]);
            $invitation->setProviderDomain($associativeArray[Schema::Invitation_provider_domain]);
            $invitation->setRecipientDomain($associativeArray[Schema::Invitation_recipient_domain]);
            $invitation->setSenderCloudId($associativeArray[Schema::Invitation_sender_cloud_id]);
            $invitation->setSenderEmail($associativeArray[Schema::Invitation_sender_email]);
            $invitation->setSenderName($associativeArray[Schema::Invitation_sender_name]);
            $invitation->setRecipientCloudId($associativeArray[Schema::Invitation_recipient_cloud_id]);
            $invitation->setRecipientEmail($associativeArray[Schema::Invitation_recipient_email]);
            $invitation->setRecipientName($associativeArray[Schema::Invitation_recipient_name]);
            $invitation->setTimestamp($associativeArray[Schema::Invitation_timestamp]);
            $invitation->setStatus($associativeArray[Schema::Invitation_status]);
            return $invitation;
        }
        $this->logger->error('Unable to create a new Invitation from associative array: ' . print_r($associativeArray, true), ['app' => RDMesh::APP_NAME]);
        return null;
    }

    /**
     * Builds and returns an array of new invitation objects from the specified the associatives array.
     * @param array $associativeArrays
     * @return array
     */
    private function newInvitations(array $associativeArrays): array
    {
        $invitations = [];
        if (isset($associativeArrays) && count($associativeArrays) > 0) {
            foreach ($associativeArrays as $associativeArray) {
                $invitation = new Invitation();
                $invitation->setId($associativeArray['id']);
                $invitation->setToken($associativeArray[Schema::Invitation_token]);
                $invitation->setProviderDomain($associativeArray[Schema::Invitation_provider_domain]);
                $invitation->setRecipientDomain($associativeArray[Schema::Invitation_recipient_domain]);
                $invitation->setSenderCloudId($associativeArray[Schema::Invitation_sender_cloud_id]);
                $invitation->setSenderEmail($associativeArray[Schema::Invitation_sender_email]);
                $invitation->setSenderName($associativeArray[Schema::Invitation_sender_name]);
                $invitation->setRecipientCloudId($associativeArray[Schema::Invitation_recipient_cloud_id]);
                $invitation->setRecipientEmail($associativeArray[Schema::Invitation_recipient_email]);
                $invitation->setRecipientName($associativeArray[Schema::Invitation_recipient_name]);
                $invitation->setTimestamp($associativeArray[Schema::Invitation_timestamp]);
                $invitation->setStatus($associativeArray[Schema::Invitation_status]);
                array_push($invitations, $invitation);
            }
        }
        return $invitations;
    }
}
