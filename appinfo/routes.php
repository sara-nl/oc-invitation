<?php
/**
 * 
 */
return [
	'routes' => [
		// bespoke API - invites
		['name' => 'invitation#generate_invite', 'url' => '/generate-invite', 'verb' => 'GET'],
		['name' => 'invitation#handle_invite', 'url' => '/handle-invite', 'verb' => 'GET'],
		['name' => 'invitation#accept_invite', 'url' => '/accept-invite', 'verb' => 'POST'],

		// bespoke API - mesh registry
		['name' => 'mesh_registry#forward_invite', 'url' => '/registry/forward-invite', 'verb' => 'GET'],

		// OCM - Open Cloud Mesh protocol
		/* @FIXME change to POST */
		['name' => 'ocm#invite_accepted', 'url' => '/ocm/invite-accepted', 'verb' => 'GET'],
	]
];
