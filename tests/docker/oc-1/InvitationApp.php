<?php

/**
 * This file replaces the lib/AppInfo/InvitationApp in the integration tests
 * and checks whether a test session user is required.
 */

namespace OCA\Invitation\AppInfo;

use Exception;
use OC\Session\Memory;
use OCA\Invitation\Federation\InvitationServiceProviderMapper;
use OCA\Invitation\Federation\InvitationMapper;
use OCA\Invitation\Federation\RemoteUserMapper;
use OCA\Invitation\Service\InvitationNotifier;
use OCA\Invitation\Service\InvitationService;
use OCA\Invitation\Service\MeshRegistry\MeshRegistryService;
use OCP\AppFramework\App;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataResponse;
use OCP\IContainer;

class InvitationApp extends App
{
    public const APP_NAME = 'invitation';

    public const CONFIG_ALLOW_SHARING_WITH_INVITED_USERS_ONLY = 'allow_sharing_with_invited_users_only';

    public function __construct()
    {
        parent::__construct(self::APP_NAME);

        $container = $this->getContainer();

        // Create a user session
        // For the integration tests this seems to work ok
        // Checks for existence of the 'User: username' header and creates a session from that.
        if (!empty($_SERVER['HTTP_USER'])) {
            $userName = $_SERVER['HTTP_USER'];
            \OC::$server->getLogger()->debug("Running test for user '$userName'.");
            \OC::$server->getUserSession()->createSessionToken(\OC::$server->getRequest(), $userName, $userName);
            $user = \OC::$server->getUserManager()->checkPassword($userName, $userName);
            \OC::$server->getUserSession()->setUser($user);
        } else {
            \OC::$server->getLogger()->debug("Running test with no user session.");
        }

        $container->registerService(
            'MeshRegistryService',
            function (IContainer $c) {
                return new MeshRegistryService(
                    self::APP_NAME,
                    $c->query('Config'),
                    new InvitationServiceProviderMapper(
                        \OC::$server->getDatabaseConnection()
                    )
                );
            }
        );
        $container->registerService(
            'InvitationService',
            function () {
                return new InvitationService(
                    new InvitationMapper(
                        \OC::$server->getDatabaseConnection()
                    ),
                    new RemoteUserMapper(
                        \OC::$server->getDatabaseConnection()
                    )
                );
            }
        );
        $container->registerService('Config', function (IContainer $c) {
            return $c->query('ServerContainer')->getConfig();
        });

        $manager = \OC::$server->getNotificationManager();
        $manager->registerNotifier(
            function () {
                return new InvitationNotifier(
                    \OC::$server->getL10NFactory(),
                    new MeshRegistryService(
                        self::APP_NAME,
                        $this->getContainer()->query('Config'),
                        new InvitationServiceProviderMapper(
                            \OC::$server->getDatabaseConnection()
                        )
                    )
                );
            },
            function () {
                return [
                    'id' => 'notification',
                    'name' => 'notification name'
                ];
            }
        );

        // All route controllers are registered automatically through owncloud's 'Automatic Dependency Assembly'
    }
}
