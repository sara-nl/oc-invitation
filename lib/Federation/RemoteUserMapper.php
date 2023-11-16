<?php

/**
 * Mapper for remote users.
 */

namespace OCA\Invitation\Federation;

use Exception;
use OCA\Invitation\Db\Schema;
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
        $userCloudID = \OC::$server->getUserSession()->getUser()->getCloudId();

        $parameter = '%' . $this->db->escapeLikeParameter($search) . '%';
        $qb = $this->db->getQueryBuilder();
        $query = $qb->select('*')->from(Schema::VIEW_REMOTEUSERS, 'i');
        $or = $qb->expr()->orX();
        $or->add($qb->expr()->iLike(Schema::REMOTEUSER_REMOTE_USER_CLOUD_ID, $qb->createPositionalParameter($parameter)));
        $or->add($qb->expr()->iLike(Schema::REMOTEUSER_REMOTE_USER_NAME, $qb->createPositionalParameter($parameter)));
        $query->where($or)
            ->andWhere($qb->expr()->eq(Schema::REMOTEUSER_USER_CLOUD_ID, $qb->createPositionalParameter($userCloudID)));

        $remoteUsers = [];
        try {
            $remoteUsers = $this->newRemoteUsers($query->execute()->fetchAllAssociative());
        } catch (Exception $e) {
            $this->logger->error('Message: ' . $e->getMessage() . ' Stacktrace: ' . $e->getTraceAsString());
            throw new Exception("Error searching for remote users with search string '$search'");
        }
        return $remoteUsers;
    }

    /**
     * Builds and returns an array of new remote users objects from the specified the associatives array.
     * @param array $associativeArrays
     * @return array
     */
    private function newRemoteUsers(array $associativeArrays): array
    {
        $remoteUsers = [];
        if (isset($associativeArrays) && count($associativeArrays) > 0) {
            foreach ($associativeArrays as $associativeArray) {
                $remoteUser = new RemoteUser();
                $remoteUser->setInvitationID($associativeArray[Schema::REMOTEUSER_INVITATION_ID]);
                $remoteUser->setUserCloudID($associativeArray[Schema::REMOTEUSER_USER_CLOUD_ID]);
                $remoteUser->setUserCloudID($associativeArray[Schema::REMOTEUSER_USER_NAME]);
                $remoteUser->setRemoteUserCloudID($associativeArray[Schema::REMOTEUSER_REMOTE_USER_CLOUD_ID]);
                $remoteUser->setRemoteUserName($associativeArray[Schema::REMOTEUSER_REMOTE_USER_NAME]);
                array_push($remoteUsers, $remoteUser);
            }
        }
        return $remoteUsers;
    }
}
