<?php

/**
 *
 */

return [
    'routes' => [
        // bespoke API - invitation
        ['name' => 'invitation#generate_invite',            'url' => '/generate-invite', 'verb' => 'GET'],
        ['name' => 'invitation#handle_invite',              'url' => '/handle-invite', 'verb' => 'GET'],
        // The following 2 methods should actually be PUT but unfortunately the notification handler that uses these to routes
        // does not set the header Content-type in the request which causing the calls to fail.
        // And so it only works with POST.
        ['name' => 'invitation#accept_invite',              'url' => '/accept-invite/{token}', 'verb' => 'POST'],
        ['name' => 'invitation#decline_invite',             'url' => '/decline-invite/{token}', 'verb' => 'POST'],
        ['name' => 'invitation#index',                      'url' => '/index', 'verb' => 'GET'],
        ['name' => 'invitation#find',                       'url' => '/find-invitation', 'verb' => 'GET'],
        ['name' => 'invitation#find_by_token',              'url' => '/find-invitation-by-token', 'verb' => 'GET'],
        ['name' => 'invitation#update',                     'url' => '/update-invitation', 'verb' => 'PUT'],
        ['name' => 'invitation#find_all',                   'url' => '/find-all-invitations', 'verb' => 'GET'],

        // bespoke API - remote user
        ['name' => 'remote_user#search',                    'url' => '/remote-user/search', 'verb' => 'GET'],
        ['name' => 'remote_user#get_remote_user',           'url' => '/remote-user', 'verb' => 'GET'],

        // bespoke API - mesh registry
        ['name' => 'mesh_registry#forward_invite',          'url' => '/registry/forward-invite', 'verb' => 'GET'],
        ['name' => 'mesh_registry#domain_providers',        'url' => '/registry/domainproviders', 'verb' => 'GET'],
        ['name' => 'mesh_registry#domain_provider',         'url' => '/registry/domainprovider', 'verb' => 'GET'],
        ['name' => 'mesh_registry#add_domain_provider',     'url' => '/registry/domainprovider', 'verb' => 'POST'],
        ['name' => 'mesh_registry#delete_domain_provider',  'url' => '/registry/domainprovider', 'verb' => 'DELETE'],
        ['name' => 'mesh_registry#get_domain',              'url' => '/registry/domain', 'verb' => 'GET'],
        // assumes the domain key exists
        ['name' => 'mesh_registry#set_domain',              'url' => '/registry/domain/{domain}', 'verb' => 'PUT'],
        // TODO: ... public info endpoint that returns relevant info of this mesh node/server
        //       returns: ... to decide

        // OCM - Open Cloud Mesh protocol
        ['name' => 'ocm#invite_accepted',                   'url' => '/ocm/invite-accepted', 'verb' => 'POST'],

        // miscellaneous endpoints
        ['name' => 'page#wayf',                             'url' => '/page/wayf', 'verb' => 'GET'],
        ['name' => 'error#invitation',                      'url' => 'error/invitation', 'verb' => 'GET'],
    ]
];
