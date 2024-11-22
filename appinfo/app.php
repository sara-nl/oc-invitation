<?php

declare(strict_types=1);

use OCA\Collaboration\AppInfo\CollaborationApp;
use OCP\Util;

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
                'id' => CollaborationApp::APP_NAME,

                // The sorting weight for the navigation.
                // The higher the number, the higher will it be listed in the navigation
                'order' => 10,

                // The route that will be shown on startup
                'href' => $urlGenerator->linkToRoute(CollaborationApp::APP_NAME . '.invitation.index'),

                // The icon that will be shown in the navigation, located in img/
                'icon' => $urlGenerator->imagePath(CollaborationApp::APP_NAME, 'handshake.svg'),

                // The application's title, used in the navigation & the settings page of your app
                'name' => OC::$server->getL10N(CollaborationApp::APP_NAME)->t('Collaboration'),
            ];
        }
    );
    $app = \OC::$server->query(\OCA\Collaboration\AppInfo\CollaborationApp::class);
    // this overrides the OC core sharedialogview.js file.
    Util::addScript(CollaborationApp::APP_NAME, 'oc/sharedialogview');
    Util::addStyle(CollaborationApp::APP_NAME, 'pure-min-css-3.0.0');
    Util::addStyle(CollaborationApp::APP_NAME, 'collaboration');
} else {
    \OC::$server->getLogger()->error('Error: not installed. Collaboration app requires: Federated File Sharing app, Notifications app', ['app' => CollaborationApp::APP_NAME]);
    $eventDispatcher->addListener('OCA\Files::loadAdditionalScripts', function () {
        script(CollaborationApp::APP_NAME, 'app-install-error');
    });
}
