<?php
/**
 * 
 */
return [
	'routes' => [
		// bespoke API - invites
		['name' => 'invitation#generate_invite', 'url' => '/generate-invite', 'verb' => 'GET'],

		// bespoke API - mesh registry
		['name' => 'mesh_registry#get_domain', 'url' => '/registry/domain', 'verb' => 'GET'],

		// OCM
		['name' => 'ocm#invite_accepted', 'url' => '/ocm/invite-accepted', 'verb' => 'POST'],
	]
];
