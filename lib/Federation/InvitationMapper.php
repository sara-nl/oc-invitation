<?php

namespace OCA\Collaboration\Federation;

use Exception;
use OCA\Collaboration\AppInfo\CollaborationApp;
use OCA\Collaboration\Db\Schema;
use OCA\Collaboration\Federation\Invitation;
use OCA\Collaboration\Federation\VInvitation;
use OCA\Collaboration\Service\NotFoundException;
use OCP\AppFramework\Db\Mapper;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;
use OCP\ILogger;

class InvitationMapper extends Mapper
{
    private ILogger $logger;

    public function __construct(IDBConnection $dbConnection)
    {
        parent::__construct($dbConnection, Schema::TABLE_INVITATIONS, Invitation::class);
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
        $qb->automaticTablePrefix(false);
        $result = $qb->select('*')
            ->from(Schema::VIEW_INVITATIONS, 'i')
            ->where($qb->expr()->eq('i.id', $qb->createNamedParameter($id)))
            ->execute()->fetchAssociative();
        if (is_array($result)) {
            return $this->getVInvitation($result);
        }
        throw new NotFoundException("Could not retrieve invitation with id $id.");
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
            $qb->automaticTablePrefix(false);
            $result = $qb->select('*')
                ->from(Schema::VIEW_INVITATIONS, 'i')
                ->where($qb->expr()->eq('i.token', $qb->createNamedParameter($token)))
                ->execute()->fetchAssociative();
            if (is_array($result) && count($result) > 0) {
                return $this->getVInvitation($result);
            }
        } catch (Exception $e) {
            $this->logger->error($e->getMessage() . ' Trace: ' . $e->getTraceAsString(), ['app' => CollaborationApp::APP_NAME]);
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
        $qb->automaticTablePrefix(false);
        $query = $qb->select('*')->from(Schema::VIEW_INVITATIONS, 'i');
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
        $query->addOrderBy(Schema::INVITATION_TIMESTAMP, 'DESC');

        return $this->getVInvitations($query->execute()->fetchAllAssociative());
    }

    /**
     * Updates the invitation according to the specified fields and values.
     * The token of the invitation must be specified as one of the fields and values.
     *
     * @param array $fieldsAndValues
     * @param string @userCloudID if set only the invitations owned by the user with this cloud ID can be updated
     * @return bool true if an invitation has been updated, false otherwise
     */
    public function updateInvitation(array $fieldsAndValues, string $userCloudID = ''): bool
    {
        try {
            $qb = $this->db->getQueryBuilder();
            $updateQuery = $qb->update(Schema::TABLE_INVITATIONS, 'i');
            if (isset($fieldsAndValues[Schema::INVITATION_TOKEN]) && count($fieldsAndValues) > 1) {
                foreach ($fieldsAndValues as $field => $value) {
                    if ($field != Schema::INVITATION_TOKEN) {
                        $updateQuery->set("i.$field", $qb->createNamedParameter($value));
                    }
                }
                $andWhere = $qb->expr()->andX();
                $andWhere->add($qb->expr()->eq('i.' . Schema::INVITATION_TOKEN, $qb->createNamedParameter($fieldsAndValues[Schema::INVITATION_TOKEN])));
                if ($userCloudID !== '') {
                    $andWhere->add($qb->expr()->eq('i.' . Schema::INVITATION_USER_CLOUD_ID, $qb->createNamedParameter($userCloudID)));
                }
                $updateQuery->where($andWhere);
                $result = $updateQuery->execute();
                if ($result === 1) {
                    return true;
                }
            }
        } catch (Exception $e) {
            $this->logger->error('updateInvitation failed with error: ' . $e->getMessage() . ' Trace: ' . $e->getTraceAsString(), ['app' => CollaborationApp::APP_NAME]);
        }
        return false;
    }

    /**
     * Delete all invitations that have one of the specified statuses.
     *
     * @param array $statusses
     * @return void
     * @throws Exception
     */
    public function deleteForStatus(array $statuses): void
    {
        try {
            $qb = $this->db->getQueryBuilder();
            $qb->delete(Schema::TABLE_INVITATIONS)
                ->where($qb->expr()->in(Schema::INVITATION_STATUS, $qb->createParameter(Schema::INVITATION_STATUS)));
            $qb->setParameter(Schema::INVITATION_STATUS, $statuses, IQueryBuilder::PARAM_STR_ARRAY);
            $qb->execute();
        } catch (Exception $e) {
            $this->logger->error($e->getMessage() . ' Trace: ' . $e->getTraceAsString(), ['app' => CollaborationApp::APP_NAME]);
            throw new Exception('An error occurred trying to delete invitations.');
        }
    }

    /**
     * Deletes all expired open invitations.
     *
     * @param int $periodSeconds the number of seconds after which open invitations will be considered expired.
     */
    public function deleteExpiredOpenInvitation(int $expirationPeriod)
    {
        try {
            $time = \time() - $expirationPeriod;
            $qb = $this->db->getQueryBuilder();
            $qb->delete(Schema::TABLE_INVITATIONS)
                ->where($qb->expr()->in(Schema::INVITATION_STATUS, $qb->createParameter(Schema::INVITATION_STATUS)))
                ->andWhere($qb->expr()->lt(Schema::INVITATION_TIMESTAMP, $qb->createParameter('time')));
            $qb->setParameter(Schema::INVITATION_STATUS, [Invitation::STATUS_OPEN], IQueryBuilder::PARAM_STR_ARRAY);
            $qb->setParameter('time', $time, IQueryBuilder::PARAM_INT);
            $qb->execute();
        } catch (Exception $e) {
            $this->logger->error($e->getMessage() . ' Trace: ' . $e->getTraceAsString(), ['app' => CollaborationApp::APP_NAME]);
            throw new Exception('An error occurred trying to delete open invitations.');
        }
    }

    /**
     * Builds and returns a new VInvitation object from specified associative array.
     *
     * @param array $associativeArray
     * @return VInvitation
     */
    private function getVInvitation(array $associativeArray): VInvitation
    {
        if (isset($associativeArray) && count($associativeArray) > 0) {
            $invitation = new VInvitation();
            $invitation->setId($associativeArray['id']);
            $invitation->setToken($associativeArray[Schema::VINVITATION_TOKEN]);
            $invitation->setTimestamp($associativeArray[Schema::INVITATION_TIMESTAMP]);
            $invitation->setStatus($associativeArray[Schema::INVITATION_STATUS]);
            $invitation->setUserCloudID($associativeArray[Schema::VINVITATION_USER_CLOUD_ID]);
            $invitation->setSentReceived($associativeArray[Schema::VINVITATION_SEND_RECEIVED]);
            $invitation->setProviderEndpoint($associativeArray[Schema::VINVITATION_PROVIDER_ENDPOINT]);
            $invitation->setRecipientEndpoint($associativeArray[Schema::VINVITATION_RECIPIENT_ENDPOINT]);
            $invitation->setSenderCloudId($associativeArray[Schema::VINVITATION_SENDER_CLOUD_ID]);
            $invitation->setSenderEmail($associativeArray[Schema::VINVITATION_SENDER_EMAIL]);
            $invitation->setSenderName($associativeArray[Schema::VINVITATION_SENDER_NAME]);
            $invitation->setRecipientCloudId($associativeArray[Schema::VINVITATION_RECIPIENT_CLOUD_ID]);
            $invitation->setRecipientEmail($associativeArray[Schema::VINVITATION_RECIPIENT_EMAIL]);
            $invitation->setRecipientName($associativeArray[Schema::VINVITATION_RECIPIENT_NAME]);
            $invitation->setRemoteUserCloudID($associativeArray[Schema::VINVITATION_REMOTE_USER_CLOUD_ID]);
            $invitation->setRemoteUserName($associativeArray[Schema::VINVITATION_REMOTE_USER_NAME]);
            $invitation->setRemoteUserEmail($associativeArray[Schema::VINVITATION_REMOTE_USER_EMAIL]);
            $invitation->setRemoteUserProviderEndpoint($associativeArray[Schema::VINVITATION_REMOTE_USER_PROVIDER_ENDPOINT]);
            $invitation->setRemoteUserProviderName($associativeArray[Schema::VINVITATION_REMOTE_USER_PROVIDER_NAME]);
            return $invitation;
        }
        $this->logger->error('Unable to create a new Invitation from associative array: ' . print_r($associativeArray, true), ['app' => CollaborationApp::APP_NAME]);
        return null;
    }

    /**
     * Builds and returns an array with new VInvitation objects from the specified associative arrays.
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
