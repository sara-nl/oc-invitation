<?php

/**
 * Implements the remote user sharees search interface.
 * 
 */

namespace OCA\RDMesh\Service;

use Exception;
use OCA\RDMesh\AppInfo\RDMesh;
use OCA\RDMesh\Federation\RemoteUserMapper;
use OCP\ILogger;
use OCP\Share;
use OCP\Share\IRemoteShareesSearch;

class RemoteUserService implements IRemoteShareesSearch
{

    private RemoteUserMapper $remoteUserMapper;
    private ILogger $logger;

    public function __construct(RemoteUserMapper $remoteUserMapper)
    {
        $this->remoteUserMapper = $remoteUserMapper;
        $this->logger = \OC::$server->getLogger();
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
        try {
            $result = [];
            // needs at least 3 characters
            if (strlen($search) < 3) {
                return $result;
            }

            // prepare non invited user
            // TODO: do we need a translation for 'Not invited' ?
            $nonInvitedUser = [
                'label' => "$search (Not invited)",
                'value' => [
                    'shareType' => Share::SHARE_TYPE_REMOTE,
                    'shareWith' => $search,
                ]
            ];
            $remoteUsers = $this->remoteUserMapper->search($search);

            foreach ($remoteUsers as $i => $remoteUser) {
                array_push($result, [
                    'label' => $remoteUser->getRemoteUserName(),
                    'value' => [
                        'shareType' => Share::SHARE_TYPE_REMOTE,
                        'shareWith' => $remoteUser->getRemoteUserCloudID(),
                    ]
                ]);
            }

            // check for non invited user
            if (strpos($search, '@') !== false && count($remoteUsers) < 1) {
                array_push($result, $nonInvitedUser);
            }

            return $result;
        } catch (Exception $e) {
            $this->logger->error('Message: ' . $e->getMessage() . ' Stacktrace: ' . $e->getTraceAsString(), ['app' => RDMesh::APP_NAME]);
            throw new ServiceException('Error searching for remote users.');
        }
    }
}
