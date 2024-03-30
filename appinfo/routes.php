<?php

/**
 *
 */

return [
    'routes' => [
        // bespoke API - invitation
        // unprotected endpoint /invitation
        ['name' => 'invitation#invitation',                 'url' => '/invite/{token}', 'verb' => 'GET'],
        ['name' => 'invitation#invitation_form',            'url' => '/invitation-form', 'verb' => 'GET'],
        ['name' => 'invitation#generate_invite',            'url' => '/generate-invite', 'verb' => 'POST'],
        ['name' => 'invitation#handle_invite',              'url' => '/handle-invite', 'verb' => 'GET'],
        ['name' => 'invitation#accept_invite',              'url' => '/accept-invite/{token}', 'verb' => 'PUT'],
        ['name' => 'invitation#decline_invite',             'url' => '/decline-invite/{token}', 'verb' => 'PUT'],
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

        // route '/registry/invitation-service-provider' concerns remote invitation service providers
        // returns the properties of the invitation service provider like endpoint, domain, name
        ['name' => 'mesh_registry#invitation_service_provider', 'url' => '/registry/invitation-service-provider', 'verb' => 'GET'],
        // adds a remote invitation service provider
        ['name' => 'mesh_registry#add_invitation_service_provider', 'url' => '/registry/invitation-service-provider', 'verb' => 'POST'],
        // @depricated remote services cannot be updated, they update themselves and we retrieve the properties from them
        ['name' => 'mesh_registry#update_invitation_service_provider', 'url' => '/registry/invitation-service-provider', 'verb' => 'PUT'],
        ['name' => 'mesh_registry#delete_invitation_service_provider', 'url' => '/registry/invitation-service-provider', 'verb' => 'DELETE'],

        // route '/registry/invitation-service-providers' returns all providers
        ['name' => 'mesh_registry#invitation_service_providers',        'url' => '/registry/invitation-service-providers', 'verb' => 'GET'],

        // route '/endpoint' of this instance
        ['name' => 'mesh_registry#get_endpoint', 'url' => '/registry/endpoint', 'verb' => 'GET'],
        ['name' => 'mesh_registry#set_endpoint', 'url' => '/registry/endpoint', 'verb' => 'PUT'],

        // route '/name' of this instance
        ['name' => 'mesh_registry#get_name', 'url' => '/registry/name', 'verb' => 'GET'],
        ['name' => 'mesh_registry#set_name', 'url' => '/registry/name', 'verb' => 'PUT'],

        // route '/share-with-invited-users-only' of this instance
        ['name' => 'mesh_registry#get_allow_sharing_with_invited_users_only', 'url' => '/share-with-invited-users-only', 'verb' => 'GET'],
        ['name' => 'mesh_registry#set_allow_sharing_with_invited_users_only', 'url' => '/share-with-invited-users-only', 'verb' => 'PUT'],

        // TODO: ... public info endpoint that returns relevant info of this mesh node/server
        //       returns: ... to decide

        // OCM - Open Cloud Mesh protocol
        ['name' => 'ocm#invite_accepted',                   'url' => '/ocm/invite-accepted', 'verb' => 'POST'],

        // miscellaneous endpoints
        ['name' => 'page#wayf',                             'url' => '/page/wayf', 'verb' => 'GET'],
        ['name' => 'error#invitation',                      'url' => 'error/invitation', 'verb' => 'GET'],
    ]
];
