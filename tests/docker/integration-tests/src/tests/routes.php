<?php

use OCA\Invitation\AppInfo\InvitationApp;
use OCA\Invitation\Controller\InvitationController;
use OCA\Invitation\Controller\MeshRegistryController;
use OCA\Invitation\ExternalApiRoute;

/**
 * This is where the we register external API routes.
 * !! Use for the integration tests only !! 
 * Running the tests requires putting this file into the 'appinfo/' folder
 * and renaming the original routes file to routes-main.php
 */

$meshRegistryService = \OC::$server->getAppContainer(InvitationApp::APP_NAME)->query('MeshRegistryService');
$meshRegistryController = new MeshRegistryController(
    InvitationApp::APP_NAME,
    \OC::$server->getRequest(),
    $meshRegistryService
);
$invitationService = \OC::$server->getAppContainer(InvitationApp::APP_NAME)->query('InvitationService');
$invitationController = new InvitationController(
    InvitationApp::APP_NAME,
    \OC::$server->getRequest(),
    $meshRegistryService,
    $invitationService
);


new ExternalApiRoute(
    'GET',
    '/apps/invitation/name',
    'getName',
    $meshRegistryController
);

new ExternalApiRoute(
    'GET',
    '/apps/invitation/endpoint',
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

// and return the original routes
return include 'routes-main.php';
