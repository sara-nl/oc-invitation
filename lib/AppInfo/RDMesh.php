<?php
/**
 * 
 */

namespace OCA\RDMesh\AppInfo;

use OCA\RDMesh\Notifier\RDMeshNotifier;
use OCA\RDMesh\Service\RDMeshService;
use OCP\AppFramework\App;
use OCP\IContainer;

class RDMesh extends App {
    public const APP_NAME = 'rd-mesh';
    
    public function __construct() {
        parent::__construct(self::APP_NAME);

        // instantiate the application configuration service
        $container = $this->getContainer();
        $container->registerService('RDMeshService', function(IContainer $c) {
            return new RDMeshService(
                self::APP_NAME,
                $c->query('Config'),
            );
        });
        $container->registerService('Config', function(IContainer $c) {
            return $c->query('ServerContainer')->getConfig();
        });

        $server = $container->getServer();
        $manager = $server->getNotificationManager();
        $manager->registerNotifier(function() use ($manager) {
            \OC::$server->getLogger()->debug(' --- registerNotifier ---');
          return new RDMeshNotifier(self::APP_NAME, \OC::$server->getL10NFactory());
        }, function () {
            $l = \OC::$server->getL10N('rd-mesh');
            return [
                'id' => 'rd-mesh',
                'name' => $l->t('Science Mesh'),
            ];
        });
        
        // All route controllers are registered automatically through owncloud's 'Automatic Dependency Assembly'

    }
}