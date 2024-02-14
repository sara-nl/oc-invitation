<?php

/**
 * Implements the remote user sharees search interface.
 *
 */

namespace OCA\Invitation\Service;

use Exception;
use OCA\Invitation\AppInfo\InvitationApp;
use OCA\Invitation\Federation\RemoteUser;
use OCA\Invitation\Federation\RemoteUserMapper;
use OCA\Invitation\Util;
use OCP\IConfig;
use OCP\IL10N;
use OCP\ILogger;
use OCP\Share;
use OCP\Share\IRemoteShareesSearch;

class RemoteUserService implements IRemoteShareesSearch
{
    public const REMOTE_USER_TYPE_INFO_INVITED = 'REMOTE_USER_TYPE_INFO_INVITED';
    public const REMOTE_USER_TYPE_INFO_UNINVITED = 'REMOTE_USER_TYPE_INFO_UNINVITED';

    private RemoteUserMapper $remoteUserMapper;
    private IConfig $config;
    private IL10N $il10n;
    private ILogger $logger;

    public function __construct(RemoteUserMapper $remoteUserMapper, IConfig $config, IL10N $il10n)
    {
        $this->remoteUserMapper = $remoteUserMapper;
        $this->config = $config;
        $this->il10n = $il10n;
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
            $opencloudmeshResult = [];

            // needs at least 3 characters
            if (strlen($search) < 3) {
                return $result;
            }

            // Consider potential results from the Open Cloud Mesh plugin
            $pluginClass = $this->config->getSystemValue('invitation.opencloudmeshRemoteShareesSearch', '\OCA\OpenCloudMesh\ShareeSearchPlugin');
            if (class_exists($pluginClass)) {
                $this->logger->debug(" - opencloudmesh app is installed, found remote sharees search implementation: $pluginClass", ['app' => InvitationApp::APP_NAME]);
                try {
                    $plugin = \OC::$server->query($pluginClass);
                    $opencloudmeshResult = $plugin->search($search);
                    // remove the remote users to prevent duplicates, because we will add them later
                    if (count($opencloudmeshResult) > 0) {
                        foreach ($opencloudmeshResult as $i => $v) {
                            if ($v['value']['shareType'] === Share::SHARE_TYPE_REMOTE) {
                                unset($opencloudmeshResult[$i]);
                            }
                        }
                        $opencloudmeshResult = array_values($opencloudmeshResult);
                    }
                } catch (Exception $e) {
                    $this->logger->error("Error retrieving opencloudmesh sharee search results: " . $e->getTraceAsString(), ['app' => InvitationApp::APP_NAME]);
                }
            } else {
                $this->logger->debug(" - skipping opencloudmesh remote sharees search, opemcloudmesh remote sharees search implementation not found: $pluginClass", ['app' => InvitationApp::APP_NAME]);
            }

            $remoteUsers = $this->remoteUserMapper->search($search);

            foreach ($remoteUsers as $i => $remoteUser) {
                array_push($result, [
                    'label' => $remoteUser->getRemoteUserName() . ' - ' . $remoteUser->getRemoteUserProviderName(),
                    /** custom field */
                    'invited' => true,
                    'value' => [
                        'shareType' => Share::SHARE_TYPE_REMOTE,
                        'shareWith' => $remoteUser->getRemoteUserCloudID(),
                        /** custom field */
                        'typeInfo' => $this->il10n->t(self::REMOTE_USER_TYPE_INFO_INVITED),
                        'shareWithAdditionalInfo' => $remoteUser->getRemoteUserEmail(),
                    ]
                ]);
            }

            // prepare non invited user
            $nonInvitedUser = [
                'label' => "$search",
                /* custom field */
                'uninvited' => true,
                'value' => [
                    'shareType' => Share::SHARE_TYPE_REMOTE,
                    'shareWith' => $search,
                    /* custom field */
                    'typeInfo' => $this->il10n->t(self::REMOTE_USER_TYPE_INFO_UNINVITED),
                ]
            ];
            if (
                Util::isTrue($this->config->getAppValue(InvitationApp::APP_NAME, InvitationApp::CONFIG_ALLOW_SHARING_WITH_INVITED_USERS_ONLY)) === false
                && strpos($search, '@') !== false
                && count($remoteUsers) < 1
            ) {
                array_push($result, $nonInvitedUser);
            }

            // and merge and return the results
            return array_merge($result, $opencloudmeshResult);
        } catch (Exception $e) {
            $this->logger->error('Message: ' . $e->getMessage() . ' Trace: ' . $e->getTraceAsString(), ['app' => InvitationApp::APP_NAME]);
            throw new ServiceException('Error searching for remote users.');
        }
    }

    /**
     * Returns the remote user with the specified cloud ID.
     * @param string $cloudID
     * @return RemoteUser
     * @throws NotFoundException in case the remote user could not be found
     * @throws Exception in case of an unexpected exception
     */
    public function getRemoteUser(string $cloudID): RemoteUser
    {
        try {
            return $this->remoteUserMapper->getRemoteUser($cloudID);
        } catch (NotFoundException $e) {
            throw $e;
        } catch (Exception $e) {
            $this->logger->error('Message: ' . $e->getMessage() . ' Trace: ' . $e->getTraceAsString(), ['app' => InvitationApp::APP_NAME]);
            throw new ServiceException('Error retrieving remote user.');
        }
    }
}
