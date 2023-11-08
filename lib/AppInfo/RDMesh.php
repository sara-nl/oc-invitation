<?php

/**
 * 
 */

namespace OCA\RDMesh\AppInfo;

use OCA\RDMesh\Federation\DomainProviderMapper;
use OCA\RDMesh\Federation\InvitationMapper;
use OCA\RDMesh\Federation\RemoteUserMapper;
use OCA\RDMesh\Service\InvitationNotifier;
use OCA\RDMesh\Service\InvitationService;
use OCA\RDMesh\Federation\Service\MeshRegistryService;
use OCP\AppFramework\App;
use OCP\IContainer;

class RDMesh extends App
{
    public const APP_NAME = 'rd-mesh';

    public const CONFIG_allow_sharing_with_non_invited_users = 'allow_sharing_with_non_invited_users';

    public function __construct()
    {
        parent::__construct(self::APP_NAME);

        // instantiate the application configuration service
        $container = $this->getContainer();
        $container->registerService(
            'MeshRegistryService',
            function (IContainer $c) {
                return new MeshRegistryService(
                    self::APP_NAME,
                    $c->query('Config'),
                    new DomainProviderMapper(
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
                        new DomainProviderMapper(
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
