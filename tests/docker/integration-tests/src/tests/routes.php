<?php

/**
 * This is where the we register external API routes.
 * !! USE FOR THE INTEGRATION TESTS ONLY !!
 * Running the tests requires putting this file into the 'appinfo/' folder
 * and renaming the original routes file to routes-main.php
 */

use OCA\Invitation\AppInfo\InvitationApp;
use OCA\Invitation\Controller\InvitationController;
use OCA\Invitation\Controller\MeshRegistryController;
use OCA\Invitation\ExternalApiRoute;

$meshRegistryService = \OC::$server->getAppContainer(InvitationApp::APP_NAME)->query('MeshRegistryService');
$meshRegistryController = new MeshRegistryController(
    InvitationApp::APP_NAME,
    \OC::$server->getRequest(),
    $meshRegistryService
);
$invitationService = \OC::$server->getAppContainer(InvitationApp::APP_NAME)->query('InvitationService');
$l10nService = \OC::$server->getAppContainer(InvitationApp::APP_NAME)->query('L10N');
$invitationController = new InvitationController(
    InvitationApp::APP_NAME,
    \OC::$server->getRequest(),
    $meshRegistryService,
    $invitationService,
    $l10nService
);


new ExternalApiRoute(
    'GET',
    '/apps/invitation/registry/name',
    'getName',
    $meshRegistryController
);

new ExternalApiRoute(
    'GET',
    '/apps/invitation/registry/endpoint',
    'getEndpoint',
    $meshRegistryController
);

new ExternalApiRoute(
    'POST',
    '/apps/invitation/generate-invite',
    'generateInvite',
    $invitationController
);
new ExternalApiRoute(
    'GET',
    '/apps/invitation/find-invitation-by-token',
    'findByToken',
    $invitationController
);
new ExternalApiRoute(
    'GET',
    '/apps/invitation/handle-invite',
    'handleInvite',
    $invitationController,
    true
);
new ExternalApiRoute(
    'GET',
    '/apps/invitation/find-invitation-by-token',
    'findByToken',
    $invitationController
);

// and return the original routes
return include 'routes-main.php';
