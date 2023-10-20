<?php

declare(strict_types=1);

use OCA\RDMesh\AppInfo\RDMesh;

require __DIR__ . '/../vendor/autoload.php';

$eventDispatcher = \OC::$server->getEventDispatcher();

if (class_exists('OCA\FederatedFileSharing\AppInfo\Application')) {
    $eventDispatcher->addListener('OCA\Files::loadAdditionalScripts', function () {
        script(RDMesh::APP_NAME, 'rd-mesh');
    });
    $app = \OC::$server->query(\OCA\RDMesh\AppInfo\RDMesh::class);
} else {
    // log and alert with the error message
    \OC::$server->getLogger()->error('Required Federated File Sharing app is not installed !', ['app' => RDMesh::APP_NAME]);
    $eventDispatcher->addListener('OCA\Files::loadAdditionalScripts', function () {
        script(RDMesh::APP_NAME, 'fed-app-error');
    });
}
