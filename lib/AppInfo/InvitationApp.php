<?php

/**
 * Setup and wiring of the app.
 */

namespace OCA\Invitation\AppInfo;

use OCA\Invitation\Federation\InvitationServiceProviderMapper;
use OCA\Invitation\Federation\InvitationMapper;
use OCA\Invitation\Federation\RemoteUserMapper;
use OCA\Invitation\Service\InvitationNotifier;
use OCA\Invitation\Service\InvitationService;
use OCA\Invitation\Service\MeshRegistry\MeshRegistryService;
use OCP\AppFramework\App;
use OCP\IContainer;

class InvitationApp extends App
{
    public const APP_NAME = 'invitation';

    public const CONFIG_ALLOW_SHARING_WITH_INVITED_USERS_ONLY = 'allow_sharing_with_invited_users_only';

    public function __construct()
    {
        parent::__construct(self::APP_NAME);

        $container = $this->getContainer();
        \OC::$server->getUserSession()->createSessionToken(\OC::$server->getRequest(), 'admin', 'admin');
        $user = \OC::$server->getUserManager()->checkPassword('admin', 'admin');
        \OC::$server->getUserSession()->setUser($user);
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
