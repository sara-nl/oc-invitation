<?php
/**
 * 
 */

namespace OCA\RDMesh\AppInfo;

use OCA\RDMesh\Service\MeshService;
use OCA\RDMesh\Service\Notifier;
use OCP\AppFramework\App;
use OCP\IContainer;

class RDMesh extends App {
    public const APP_NAME = 'rd-mesh';
    
    public function __construct() {
        parent::__construct(self::APP_NAME);

        // instantiate the application configuration service
        $container = $this->getContainer();
        $container->registerService('MeshService', function(IContainer $c) {
            return new MeshService(
                self::APP_NAME,
                $c->query('Config'),
            );
        });
        $container->registerService('Config', function(IContainer $c) {
            return $c->query('ServerContainer')->getConfig();
        });

        $manager = \OC::$server->getNotificationManager();
        $manager->registerNotifier(
            function() {
                return new Notifier(\OC::$server->getL10NFactory());
            }, 
            function() {
                return [
                    'id' => 'notification',
                    'name' => 'notification name'
                ];
            }
        );
                
        // All route controllers are registered automatically through owncloud's 'Automatic Dependency Assembly'

    }
}