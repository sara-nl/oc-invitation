<?php

declare(strict_types=1);

use OCA\RDMesh\AppInfo\RDMesh;

// FIXME: SHOULD this be necessary ?
require __DIR__ . '/../vendor/autoload.php';

$eventDispatcher = \OC::$server->getEventDispatcher();

if (
    class_exists('OCA\FederatedFileSharing\AppInfo\Application')
    && class_exists('\OCA\Notifications\AppInfo\Application')
) {
    $urlGenerator = OC::$server->getURLGenerator();
    OC::$server->getNavigationManager()->add(
        function () {
            $urlGenerator = OC::$server->getURLGenerator();

            return [
                // The string under which your app will be referenced in owncloud
                'id' => RDMesh::APP_NAME,

                // The sorting weight for the navigation.
                // The higher the number, the higher will it be listed in the navigation
                'order' => 10,

                // The route that will be shown on startup
                'href' => $urlGenerator->linkToRoute(RDMesh::APP_NAME . '.invitation.index'),

                // The icon that will be shown in the navigation, located in img/
                'icon' => $urlGenerator->imagePath(RDMesh::APP_NAME, 'handshake.jpg'),

                // The application's title, used in the navigation & the settings page of your app
                'name' => OC::$server->getL10N(RDMesh::APP_NAME)->t('Invitation'),
            ];
        }
    );

    $app = \OC::$server->query(\OCA\RDMesh\AppInfo\RDMesh::class);
} else {
    \OC::$server->getLogger()->error('Error: not installed. Invitations app requires: Federated File Sharing app, Notifications app', ['app' => RDMesh::APP_NAME]);
    $eventDispatcher->addListener('OCA\Files::loadAdditionalScripts', function () {
        script(RDMesh::APP_NAME, 'app-install-error');
    });
}
