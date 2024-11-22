<?php

/**
 * Setup and wiring of the app.
 */

namespace OCA\Collaboration\AppInfo;

use OCA\Collaboration\Federation\InvitationServiceProviderMapper;
use OCA\Collaboration\Federation\InvitationMapper;
use OCA\Collaboration\Service\InvitationNotifier;
use OCA\Collaboration\Service\InvitationService;
use OCA\Collaboration\Service\MeshRegistry\MeshRegistryService;
use OCP\AppFramework\App;
use OCP\IContainer;

class CollaborationApp extends App
{
    public const APP_NAME = 'collaboration';

    public const CONFIG_ALLOW_SHARING_WITH_INVITED_USERS_ONLY = 'allow_sharing_with_invited_users_only';

    public const CONFIG_DEPLOY_MODE = 'deploy_mode';
    public const DEPLOY_MODE_TEST = 'deploy_mode_test';

    public const INVITATION_EMAIL_SUBJECT = 'INVITATION_EMAIL_SUBJECT';

    public function __construct()
    {
        parent::__construct(self::APP_NAME);

        $container = $this->getContainer();

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
                    )
                );
            }
        );
        $container->registerService('Config', function (IContainer $c) {
            return $c->query('ServerContainer')->getConfig();
        });

        $container->registerService('L10N', function ($c) {
            return $c->query('ServerContainer')->getL10N($c->getAppName());
        });

        $manager = \OC::$server->getNotificationManager();
        $manager->registerNotifier(
            function () {
                return $this->getContainer()->query(InvitationNotifier::class);
            },
            function () {
                return [
                    'id' => 'collaboration',
                    'name' => 'Collaboration app'
                ];
            }
        );

        // All route controllers are registered automatically through owncloud's 'Automatic Dependency Assembly'
    }
}
