<?php

/**
 *
 */

return [
    'routes' => [
        // bespoke API - invites
        ['name' => 'invitation#generate_invite', 'url' => '/generate-invite', 'verb' => 'GET'],
        ['name' => 'invitation#handle_invite', 'url' => '/handle-invite', 'verb' => 'GET'],
        ['name' => 'invitation#accept_invite', 'url' => '/accept-invite', 'verb' => 'GET'],
        ['name' => 'invitation#decline_invite', 'url' => '/decline-invite', 'verb' => 'PUT'],
        ['name' => 'invitation#index', 'url' => '/index', 'verb' => 'GET'],

        ['name' => 'invitation#find', 'url' => '/find-invitation', 'verb' => 'GET'],
        ['name' => 'invitation#find_by_token', 'url' => '/find-invitation-by-token', 'verb' => 'GET'],
        ['name' => 'invitation#update', 'url' => '/update-invitation', 'verb' => 'PUT'],
        ['name' => 'invitation#find_all', 'url' => '/find-all-invitations', 'verb' => 'GET'],

        ['name' => 'remote_user#search', 'url' => '/remote-user/search', 'verb' => 'GET'],
        ['name' => 'remote_user#get_remote_user', 'url' => '/remote-user', 'verb' => 'GET'],

        // bespoke API - mesh registry
        ['name' => 'mesh_registry#forward_invite', 'url' => '/registry/forward-invite', 'verb' => 'GET'],
        ['name' => 'mesh_registry#providers', 'url' => '/registry/providers', 'verb' => 'GET'],
        // TODO: public info endpoint that returns relevant info of this mesh node/server
        //       returns: ... to decide

        // OCM - Open Cloud Mesh protocol
        ['name' => 'ocm#invite_accepted', 'url' => '/ocm/invite-accepted', 'verb' => 'POST'],

        // private API
        ['name' => 'page#wayf', 'url' => '/page/wayf', 'verb' => 'GET'],

        ['name' => 'error#invitation', 'url' => 'error/invitation', 'verb' => 'GET'],
    ]
];
