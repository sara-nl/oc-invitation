<?php
/**
 * 
 */

namespace OCA\RDMesh\AppInfo;

use OCA\RDMesh\Controller\InvitationController;
use OCA\RDMesh\Controller\MeshRegistryController;
use OCP\AppFramework\App;

class RDMesh extends App {
    public const APP_NAME = 'rd-mesh';
    
    public function __construct()
    {
        parent::__construct(self::APP_NAME);
        $container = $this->getContainer();
        $container->registerService('InvitationController', function($c) {
            return new InvitationController(
                $c->query('AppName'), 
                $c->query('Request')
            );
        });

        $container->registerService('MeshRegistryController', function($c) {
            return new MeshRegistryController(
                $c->query('AppName'), 
                $c->query('Request')
            );
        });
    }
}