<?php
/**
 * 
 */

namespace OCA\RDMesh\AppInfo;

use OCA\RDMesh\Controller\PageController;
use OCP\AppFramework\App;

class RDMesh extends App {
    public const APP_NAME = 'rd-mesh';
    
    public function __construct()
    {
        parent::__construct(self::APP_NAME);
        $container = $this->getContainer();
        $container->registerService('PageController', function($c) {
            return new PageController(
                $c->query('AppName'), 
                $c->query('Request')
            );
        });
    }
}