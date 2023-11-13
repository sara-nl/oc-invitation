<?php

namespace OCA\Invitation\Federation;

use Exception;
use OCA\Invitation\AppInfo\InvitationApp;
use OCA\Invitation\Db\Schema;
use OCA\Invitation\Federation\Invitation;
use OCA\Invitation\Federation\VInvitation;
use OCA\Invitation\Service\NotFoundException;
use OCP\AppFramework\Db\Mapper;
use OCP\IDBConnection;
use OCP\ILogger;

class InvitationMapper extends Mapper
{
    private ILogger $logger;

    public function __construct(IDBConnection $dbConnection)
    {
        parent::__construct($dbConnection, Schema::Table_Invitations, Invitation::class);
        $this->logger = \OC::$server->getLogger();
    }

    /**
     * Returns the invitation with the specified id, or NotFoundException if it could not be found.

     * @param int $id
     * @return mixed
     * @throws NotFoundException
     */
    public function find(int $id)
    {
        $qb = $this->db->getQueryBuilder();
        $result = $qb->select('*')
            ->from(Schema::View_Invitations, 'i')
            ->where($qb->expr()->eq('i.id', $qb->createNamedParameter($id)))
            ->execute()->fetchAssociative();
        if (is_array($result)) {
            return $this->getVInvitation($result);
        }
        throw new NotFoundException("Could not retrieve invitation with id '$id'.");
    }

    /** Returns the invitation with the specified token, or NotFoundException if it could not be found.
     * 
     * @param string $token
     * @return VInvitation
     * @throws NotFoundException
     */
    public function findByToken(string $token)
    {
        try {
            $qb = $this->db->getQueryBuilder();
            $result = $qb->select('*')
                ->from(Schema::View_Invitations, 'i')
                ->where($qb->expr()->eq('i.token', $qb->createNamedParameter($token)))
                ->execute()->fetchAssociative();
            if (is_array($result) && count($result) > 0) {
                return $this->getVInvitation($result);
            }
        } catch (Exception $e) {
            $this->logger->error($e->getMessage() . ' Stacktrace: ' . $e->getTraceAsString(), ['app' => InvitationApp::APP_NAME]);
            throw new NotFoundException($e->getMessage());
        }
        throw new NotFoundException("Invitation not found for token $token");
    }

    /**
     * Returns all invitations matching the specified criteria.
     * Expected $criteria format:
     * [
     *   column_1 => [value1, value2],
     *   column_2 => [value3],
     *   ... etc.
     * ] 
     * Will yield the following SQL:
     *  SELECT * WHERE (column_1 = value1 OR column_1 = value2) AND (column_2 = value3) AND (...etc.)
     * 
     * @param array $criteria
     * @return array the invitations
     */
    public function findAll(array $criteria): array
    {
        // first bundle fields and values
        $fieldsAndValues = [];
        foreach ($criteria as $fieldAndValue) {
            $key = array_keys($fieldAndValue)[0];
            $value = $fieldAndValue[$key];
            if (isset($fieldsAndValues[$key])) {
                array_push($fieldsAndValues[$key], $value);
            } else {
                $values = [];
                array_push($values, $value);
                $fieldsAndValues[$key] = $values;
            }
        }

        $qb = $this->db->getQueryBuilder();
        $query = $qb->select('*')->from(Schema::View_Invitations, 'i');
        $i = 0;
        foreach ($fieldsAndValues as $field => $values) {
            if ($i == 0) {
                $or = $qb->expr()->orX();
                foreach ($values as $value) {
                    $or->add($qb->expr()->eq("i.$field", $qb->createNamedParameter($value)));
                }
                $query->where($or);
            } else {
                $or = $qb->expr()->orX();
                foreach ($values as $value) {
                    $or->add($qb->expr()->eq("i.$field", $qb->createNamedParameter($value)));
                }
                $query->andWhere($or);
            }
            ++$i;
        }

        return $this->getVInvitations($query->execute()->fetchAllAssociative());
    }

    /**
     * Updates the invitation according to the specified fields and values.
     * The id of the invitation must be specified as one of the fields and values.
     * 
     * @param array $fieldsAndValues
     * @param string @userCloudID if set only the invitations owned by the user with this cloud ID can be updated
     * @return bool true if an invitation has been updated, false otherwise
     */
    public function updateInvitation(array $fieldsAndValues, string $userCloudID = ''): bool
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
                $andWhere = $qb->expr()->andX();
                $andWhere->add($qb->expr()->eq('i.id', $qb->createNamedParameter($fieldsAndValues['id'])));
                if($userCloudID !== '') {
                    $andWhere->add($qb->expr()->eq('i.' . Schema::Invitation_user_cloud_id, $qb->createNamedParameter($userCloudID)));
                }
                $updateQuery->where($andWhere);
                $result = $updateQuery->execute();
                if ($result === 1) {
                    return true;
                }
            }
        } catch (Exception $e) {
            $this->logger->error('updateInvitation failed with error: ' . $e->getMessage() . ' Trace: ' . $e->getTraceAsString(), ['app' => InvitationApp::APP_NAME]);
        }
        return false;
    }

    /**
     * Builds and returns a new VInvitation from specified associative array.
     * 
     * @param array $associativeArray
     * @return VInvitation
     */
    private function getVInvitation(array $associativeArray): VInvitation
    {
        if (isset($associativeArray) && count($associativeArray) > 0) {
            $invitation = new VInvitation();
            $invitation->setId($associativeArray['id']);
            $invitation->setToken($associativeArray[Schema::VInvitation_token]);
            $invitation->setTimestamp($associativeArray[Schema::Invitation_timestamp]);
            $invitation->setStatus($associativeArray[Schema::Invitation_status]);
            $invitation->setUserCloudID($associativeArray[Schema::VInvitation_user_cloud_id]);
            $invitation->setSentReceived($associativeArray[Schema::VInvitation_sent_received]);
            $invitation->setProviderDomain($associativeArray[Schema::VInvitation_provider_domain]);
            $invitation->setRecipientDomain($associativeArray[Schema::VInvitation_recipient_domain]);
            $invitation->setSenderCloudId($associativeArray[Schema::VInvitation_sender_cloud_id]);
            $invitation->setSenderEmail($associativeArray[Schema::VInvitation_sender_email]);
            $invitation->setSenderName($associativeArray[Schema::VInvitation_sender_name]);
            $invitation->setRecipientCloudId($associativeArray[Schema::VInvitation_recipient_cloud_id]);
            $invitation->setRecipientEmail($associativeArray[Schema::VInvitation_recipient_email]);
            $invitation->setRecipientName($associativeArray[Schema::VInvitation_recipient_name]);
            $invitation->setRemoteUserCloudID($associativeArray[Schema::VInvitation_remote_user_cloud_id]);
            $invitation->setRemoteUserName($associativeArray[Schema::VInvitation_remote_user_name]);
            $invitation->setRemoteUserEmail($associativeArray[Schema::VInvitation_remote_user_email]);
            return $invitation;
        }
        $this->logger->error('Unable to create a new Invitation from associative array: ' . print_r($associativeArray, true), ['app' => InvitationApp::APP_NAME]);
        return null;
    }

    /**
     * Builds and returns an array with new VInvitations from the specified associative arrays.
     * 
     * @param array $associativeArrays
     * @return array
     */
    private function getVInvitations(array $associativeArrays): array
    {
        $invitations = [];
        if (isset($associativeArrays) && count($associativeArrays) > 0) {
            foreach ($associativeArrays as $associativeArray) {
                array_push($invitations, $this->getVInvitation($associativeArray));
            }
        }
        return $invitations;
    }
}
