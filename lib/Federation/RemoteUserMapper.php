<?php

/**
 * Mapper for remote users.
 */

namespace OCA\Invitation\Federation;

use Exception;
use OCA\Invitation\AppInfo\InvitationApp;
use OCA\Invitation\Db\Schema;
use OCA\Invitation\Service\NotFoundException;
use OCP\AppFramework\Db\Mapper;
use OCP\IDBConnection;
use OCP\ILogger;

class RemoteUserMapper extends Mapper
{
    private ILogger $logger;

    public function __construct(IDBConnection $dbConnection)
    {
        parent::__construct($dbConnection, Schema::VIEW_REMOTEUSERS, RemoteUser::class);
        $this->logger = \OC::$server->getLogger();
    }

    /**
     * Returns remote user entites, as specified in OCP\Share\IRemoteShareesSearch.search(),
     * that match the search criterium.
     * We search in remote user cloud ID and name.
     *
     * @param string $search the string to search for
     * @throws Exception in case of error
     */
    public function search(string $search): array
    {
        // allow search in the context of the current logged in user only
        $userCloudID = \OC::$server->getUserSession()->getUser()->getCloudId();

        $parameter = '%' . $this->db->escapeLikeParameter($search) . '%';
        $qb = $this->db->getQueryBuilder();
        $query = $qb->select('*')->from(Schema::VIEW_REMOTEUSERS, 'i');
        $or = $qb->expr()->orX();
        $or->add($qb->expr()->iLike(Schema::REMOTEUSER_REMOTE_USER_EMAIL, $qb->createPositionalParameter($parameter)));
        $or->add($qb->expr()->iLike(Schema::REMOTEUSER_REMOTE_USER_NAME, $qb->createPositionalParameter($parameter)));
        $or->add($qb->expr()->iLike(Schema::REMOTEUSER_REMOTE_USER_PROVIDER_NAME, $qb->createPositionalParameter($parameter)));
        $query->where($or)
            ->andWhere($qb->expr()->eq(Schema::REMOTEUSER_USER_CLOUD_ID, $qb->createPositionalParameter($userCloudID)));

        $remoteUsers = [];
        try {
            $remoteUsers = $this->createRemoteUsers($query->execute()->fetchAllAssociative());
        } catch (Exception $e) {
            $this->logger->error('Message: ' . $e->getMessage() . ' Trace: ' . $e->getTraceAsString(), ['app' => InvitationApp::APP_NAME]);
            throw new Exception("Error searching for remote users with search string '$search'");
        }
        return $remoteUsers;
    }

    /**
     * Returns the remote user with the specified cloud ID in the context of the current logged in user.
     * @param string $cloudID
     * @return RemoteUser
     * @throws NotFoundException if the remote user could not be found
     * @throws Exception if an exception occurred
     */
    public function getRemoteUser(string $cloudID): RemoteUser
    {
        try {
            $userCloudID = \OC::$server->getUserSession()->getUser()->getCloudId();
            $qb = $this->db->getQueryBuilder();
            $query = $qb->select('*')->from(Schema::VIEW_REMOTEUSERS, 'i');
            $and = $qb->expr()->andX();
            $and->add($qb->expr()->eq(Schema::REMOTEUSER_USER_CLOUD_ID, $qb->createPositionalParameter($userCloudID)));
            $and->add($qb->expr()->eq(Schema::REMOTEUSER_REMOTE_USER_CLOUD_ID, $qb->createPositionalParameter($cloudID)));
            $result = $query->where($and)->execute()->fetchAssociative();
            $remoteUser = null;
            if (is_array($result) && count($result) > 0) {
                $remoteUser = $this->createRemoteUser($result);
            }
            if ($remoteUser == null) {
                throw new NotFoundException("Could not retrieve remote user with cloudID '$cloudID'.");
            }
            return $remoteUser;
        } catch (Exception $e) {
            $this->logger->error("Could not retrieve remote user with cloudID '$cloudID'. Stack trace: " . $e->getTraceAsString(), ['app' => InvitationApp::APP_NAME]);
            throw $e;
        }
    }

    /**
     * Builds and returns an array of new RemoteUser objects from the specified associatives array.
     * @param array $associativeArrays
     * @return array
     */
    private function createRemoteUsers(array $associativeArrays): array
    {
        $remoteUsers = [];
        if (isset($associativeArrays) && count($associativeArrays) > 0) {
            foreach ($associativeArrays as $associativeArray) {
                array_push($remoteUsers, $this->createRemoteUser($associativeArray));
            }
        }
        return $remoteUsers;
    }

    /**
     * Builds and returns a new RemoteUser objects from the specified associative arrays.
     *
     * @param array $associativeArray
     * @return RemoteUser
     */
    private function createRemoteUser(array $associativeArray): RemoteUser
    {
        if (count($associativeArray) > 0) {
            $remoteUser = new RemoteUser();
            $remoteUser->setInvitationID($associativeArray[Schema::REMOTEUSER_INVITATION_ID]);
            $remoteUser->setUserCloudID($associativeArray[Schema::REMOTEUSER_USER_CLOUD_ID]);
            $remoteUser->setUserName($associativeArray[Schema::REMOTEUSER_USER_NAME]);
            $remoteUser->setRemoteUserCloudID($associativeArray[Schema::REMOTEUSER_REMOTE_USER_CLOUD_ID]);
            $remoteUser->setRemoteUserName($associativeArray[Schema::REMOTEUSER_REMOTE_USER_NAME]);
            $remoteUser->setRemoteUserEmail($associativeArray[Schema::REMOTEUSER_REMOTE_USER_EMAIL]);
            $remoteUser->setRemoteUserProviderEndpoint($associativeArray[Schema::REMOTEUSER_REMOTE_USER_PROVIDER_ENDPOINT]);
            $remoteUser->setRemoteUserProviderName($associativeArray[Schema::REMOTEUSER_REMOTE_USER_PROVIDER_NAME]);
            return $remoteUser;
        }
        return null;
    }
}
