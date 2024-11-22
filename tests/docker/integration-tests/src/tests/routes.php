<?php

/**
 * This is where the we register external API routes.
 * !! USE FOR THE INTEGRATION TESTS ONLY !!
 * Running the tests requires putting this file into the 'appinfo/' folder
 * and renaming the original routes file to routes-main.php
 */

use OCA\Collaboration\AppInfo\CollaborationApp;
use OCA\Collaboration\Controller\InvitationController;
use OCA\Collaboration\Controller\MeshRegistryController;
use OCA\Collaboration\ExternalApiRoute;

$meshRegistryService = \OC::$server->getAppContainer(CollaborationApp::APP_NAME)->query('MeshRegistryService');
$meshRegistryController = new MeshRegistryController(
    CollaborationApp::APP_NAME,
    \OC::$server->getRequest(),
    $meshRegistryService
);
$invitationService = \OC::$server->getAppContainer(CollaborationApp::APP_NAME)->query('InvitationService');
$l10nService = \OC::$server->getAppContainer(CollaborationApp::APP_NAME)->query('L10N');
$invitationController = new InvitationController(
    CollaborationApp::APP_NAME,
    \OC::$server->getRequest(),
    $meshRegistryService,
    $invitationService,
    $l10nService
);


new ExternalApiRoute(
    'GET',
    '/apps/collaboration/registry/name',
    'getName',
    $meshRegistryController
);

new ExternalApiRoute(
    'GET',
    '/apps/collaboration/registry/endpoint',
    'getEndpoint',
    $meshRegistryController
);

new ExternalApiRoute(
    'POST',
    '/apps/collaboration/generate-invite',
    'generateInvite',
    $invitationController
);
new ExternalApiRoute(
    'GET',
    '/apps/collaboration/find-invitation-by-token',
    'findByToken',
    $invitationController
);
new ExternalApiRoute(
    'GET',
    '/apps/collaboration/handle-invite',
    'handleInvite',
    $invitationController,
    true
);
new ExternalApiRoute(
    'GET',
    '/apps/collaboration/find-invitation-by-token',
    'findByToken',
    $invitationController
);

// and return the original routes
return include 'routes-main.php';
