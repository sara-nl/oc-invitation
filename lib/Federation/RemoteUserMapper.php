<?php

/**
 * 
 */

namespace OCA\RDMesh\Federation;

use Exception;
use OCA\RDMesh\Db\Schema;
use OCP\AppFramework\Db\Mapper;
use OCP\IDb;
use OCP\Share;
use OCP\ILogger;

class RemoteUserMapper extends Mapper
{
    private ILogger $logger;

    public function __construct(IDb $dbConnection)
    {
        parent::__construct($dbConnection, Schema::Table_RemoteUsers, RemoteUser::class);
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
        $result = [];

        $parameter = '%' . $this->db->escapeLikeParameter($search) . '%';
        $qb = $this->db->getQueryBuilder();
        $query = $qb->select('*')->from(Schema::Table_RemoteUsers, 'i');
        $query->where($qb->expr()->iLike(Schema::RemoteUser_remote_user_cloud_id, $qb->createPositionalParameter($parameter)))
            ->orWhere($qb->expr()->iLike(Schema::RemoteUser_remote_user_name, $qb->createPositionalParameter($parameter)));

        $remoteUsers = [];
        try {
            $remoteUsers = $this->newRemoteUsers($query->execute()->fetchAllAssociative());
        } catch (Exception $e) {
            $this->logger->error('Message: ' . $e->getMessage() . ' Stacktrace: ' . $e->getTraceAsString());
            throw new Exception("Error searching for remote users with search string '$search'");
        }
        foreach ($remoteUsers as $i => $remoteUser) {
            array_push($result, [
                'label' => $remoteUser->getRemoteUserName() . '(' . $remoteUser->getRemoteUserCloudID() . ')',
                'value' => [
                    'shareType' => Share::SHARE_TYPE_REMOTE,
                    'shareWith' => $remoteUser->getRemoteUserCloudID(),
                ]
            ]);
        }

        return $result;
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
                $remoteUser->setInvitationID($associativeArray[Schema::RemoteUser_invitation_id]);
                $remoteUser->setUserCloudID($associativeArray[Schema::RemoteUser_user_cloud_id]);
                $remoteUser->setUserCloudID($associativeArray[Schema::RemoteUser_user_name]);
                $remoteUser->setRemoteUserCloudID($associativeArray[Schema::RemoteUser_remote_user_cloud_id]);
                $remoteUser->setRemoteUserName($associativeArray[Schema::RemoteUser_remote_user_name]);
                array_push($remoteUsers, $remoteUser);
            }
        }
        return $remoteUsers;
    }
}
