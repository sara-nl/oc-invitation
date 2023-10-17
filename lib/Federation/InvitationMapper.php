<?php

namespace OCA\RDMesh\Federation;

use Exception;
use OCA\RDMesh\AppInfo\RDMesh;
use OCA\RDMesh\Db\Schema;
use OCA\RDMesh\Federation\Invitation;
use OCP\AppFramework\Db\Mapper;
use OCP\IDb;
use OCP\ILogger;

class InvitationMapper extends Mapper
{
    private IDb $dbConnection;
    private ILogger $logger;

    public function __construct(IDb $dbConnection)
    {
        parent::__construct($dbConnection, Schema::Table_Invitations, Invitation::class);
        $this->dbConnection = $dbConnection;
        $this->logger = \OC::$server->getLogger();
    }

    /**
     * Returns the invitation with the specified id, or null if it could not be found.

     * @param int $id
     * @return mixed
     */
    public function find(int $id)
    {
        $qb = $this->dbConnection->getQueryBuilder();
        $result = $qb->select('*')
            ->from(Schema::Table_Invitations, 'i')
            ->where($qb->expr()->eq('i.id', $qb->createNamedParameter($id)))
            ->execute()->fetchAssociative();
        if (is_array($result)) {
            return $this->newInvitation($result);
        }
        return null;
    }

    /** Returns the invitation with the specified token, or null if it could not be found.
     * 
     * @param string $token
     * @return mixed
     */
    public function findByToken(string $token)
    {
        $qb = $this->dbConnection->getQueryBuilder();
        $result = $qb->select('*')
            ->from(Schema::Table_Invitations, 'i')
            ->where($qb->expr()->eq('i.token', $qb->createNamedParameter($token)))
            ->execute()->fetchAssociative();
        if (is_array($result)) {
            return $this->newInvitation($result);
        }
        return null;
    }

    public function updateInvitation(array $fieldsAndValues): bool
    {
        try {
            $qb = $this->dbConnection->getQueryBuilder();
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
            $invitation->setProviderDomain($associativeArray[Schema::Invitation_provider_domain]);
            $invitation->setSenderCloudId($associativeArray[Schema::Invitation_sender_cloud_id]);
            $invitation->setSenderEmail($associativeArray[Schema::Invitation_sender_email]);
            $invitation->setSenderName($associativeArray[Schema::Invitation_sender_name]);
            $invitation->setRecipientCloudId($associativeArray[Schema::Invitation_recipient_cloud_id]);
            $invitation->setRecipientEmail($associativeArray[Schema::Invitation_recipient_email]);
            $invitation->setRecipientName($associativeArray[Schema::Invitation_recipient_name]);
            $invitation->setStatus($associativeArray[Schema::Invitation_status]);
            $invitation->setTimestamp($associativeArray[Schema::Invitation_timestamp]);
            $invitation->setToken($associativeArray[Schema::Invitation_token]);
            return $invitation;
        }
        $this->logger->error('Unable to create a new Invitation from associative array: ' . print_r($associativeArray, true), ['app' => RDMesh::APP_NAME]);
        return null;
    }
}
