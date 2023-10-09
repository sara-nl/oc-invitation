<?php
/**
 * 
 */
return [
	'routes' => [
		// bespoke API - invites
		['name' => 'invitation#generate_invite', 'url' => '/generate-invite', 'verb' => 'GET'],
		['name' => 'invitation#handle_invite', 'url' => '/handle-invite', 'verb' => 'GET'],
		// TODO: change method to POST ...? How to create a notification POST action ?!
		['name' => 'invitation#accept_invite', 'url' => '/accept-invite', 'verb' => 'GET'],

		// bespoke API - mesh registry
		['name' => 'registry#invite_link', 'url' => '/invite-link', 'verb' => 'GET'],
		['name' => 'mesh_registry#forward_invite', 'url' => '/registry/forward-invite', 'verb' => 'GET'],
		// TODO: public info endpoint that returns relevant info of this mesh node/server
		//		 returns: full host url (=trusted server url), name, logo, ...

		// OCM - Open Cloud Mesh protocol
		/* FIXME change to POST */
		['name' => 'ocm#invite_accepted', 'url' => '/ocm/invite-accepted', 'verb' => 'POST'],

		// private API
		['name' => 'page#wayf', 'url' => '/page/wayf', 'verb' => 'GET'],
	]
];
