<?php

/**
 * Translatable errors that may be returned or used as exception messages.
 */

namespace OCA\Invitation\AppInfo;

class AppError
{

    /** unspecified error */
    public const ERROR = 'ERROR';

    /* route errors */
    /** the request is missing a required parameter */
    public const REQUEST_MISSING_PARAMETER = 'REQUEST_MISSING_PARAMETER';

    /** the invitation cannot be found */
    public const INVITATION_NOT_FOUND = 'INVITATION_NOT_FOUND';

    /* Create invitation errors */
    /** unspecified create invitation error */
    public const CREATE_INVITATION_ERROR = 'CREATE_INVITATION_ERROR';
    /** the recipient's mail is required */
    public const CREATE_INVITATION_NO_RECIPIENT_EMAIL = 'CREATE_INVITATION_NO_RECIPIENT_EMAIL';
    /** a sender name is required */
    public const CREATE_INVITATION_NO_SENDER_NAME = 'CREATE_INVITATION_NO_SENDER_NAME';
    /** an invitation already exists */
    public const CREATE_INVITATION_EXISTS = 'CREATE_INVITATION_EXISTS';

    /* Handle recieved invitation errors */
    /** unspecified handle invitation error */
    public const HANDLE_INVITATION_ERROR = 'HANDLE_INVITATION_ERROR';
    /** an invitation already exists */
    public const HANDLE_INVITATION_EXISTS = 'HANDLE_INVITATION_EXISTS';
    /** invitation is missing the token */
    public const HANDLE_INVITATION_MISSING_TOKEN = 'HANDLE_INVITATION_MISSING_TOKEN';
    /** invitation is missing the provider endpoint */
    public const HANDLE_INVITATION_MISSING_PROVIDER_ENDPOINT = 'HANDLE_INVITATION_MISSING_PROVIDER_ENDPOINT';
    /** invitation is missing the sender name */
    public const HANDLE_INVITATION_MISSING_SENDER_NAME = 'HANDLE_INVITATION_MISSING_SENDER_NAME';
    /** The invitation service provider of the invitation is unknown */
    public const HANDLE_INVITATION_PROVIDER_UNKNOWN = 'HANDLE_INVITATION_PROVIDER_UNKNOWN';

    /* Mesh Registry errors */
    /** error retrieving all providers */
    public const MESH_REGISTRY_ALL_PROVIDERS_ERROR = 'MESH_REGISTRY_ALL_PROVIDERS_ERROR';
    /** error adding the invitation service provider */
    public const MESH_REGISTRY_ADD_PROVIDER_ERROR = 'MESH_REGISTRY_ADD_PROVIDER_ERROR';
    /** error deleting the invitation service provider */
    public const MESH_REGISTRY_DELETE_PROVIDER_ERROR = 'MESH_REGISTRY_DELETE_PROVIDER_ERROR';
    /** error retrieving the invitation service provider */
    public const MESH_REGISTRY_GET_PROVIDER_ERROR = 'MESH_REGISTRY_GET_PROVIDER_ERROR';
    /** error getting the endpoint */
    public const MESH_REGISTRY_GET_ENDPOINT_ERROR = 'MESH_REGISTRY_GET_ENDPOINT_ERROR';
    /** error setting the name */
    public const MESH_REGISTRY_SET_NAME_ERROR = 'MESH_REGISTRY_SET_NAME_ERROR';
    /** error getting the name */
    public const MESH_REGISTRY_GET_NAME_ERROR = 'MESH_REGISTRY_GET_NAME_ERROR';
    /** error setting the endpoint */
    public const MESH_REGISTRY_SET_ENDPOINT_ERROR = 'MESH_REGISTRY_SET_ENDPOINT_ERROR';
    /** error setting the allow_sharing_with_invited_users_only config param */
    public const MESH_REGISTRY_SET_ALLOW_SHARING_WITH_INVITED_USERS_ONLY_ERROR = 'MESH_REGISTRY_SET_ALLOW_SHARING_WITH_INVITED_USERS_ONLY_ERROR';
    /** error getting the allow_sharing_with_invited_users_only config param */
    public const MESH_REGISTRY_GET_ALLOW_SHARING_WITH_INVITED_USERS_ONLY_ERROR = 'MESH_REGISTRY_GET_ALLOW_SHARING_WITH_INVITED_USERS_ONLY_ERROR';
    /** error updating the invitation service provider, endpoint is required */
    public const MESH_REGISTRY_UPDATE_PROVIDER_ENDPOINT_NOT_SET_ERROR = 'MESH_REGISTRY_UPDATE_PROVIDER_ENDPOINT_NOT_SET_ERROR';
    /** error updating the this invitation service provider, route not allowed */
    public const MESH_REGISTRY_UPDATE_PROVIDER_ROUTE_NOT_ALLOWED_ERROR = 'MESH_REGISTRY_UPDATE_PROVIDER_ROUTE_NOT_ALLOWED_ERROR';
    /** /registry/invitation-service-provider response is invalid */
    public const MESH_REGISTRY_ENDPOINT_INVITATION_SERVICE_PROVIDER_RESPONSE_INVALID = 'MESH_REGISTRY_ENDPOINT_INVITATION_SERVICE_PROVIDER_RESPONSE_INVALID';

    /* OCM invite errors */
    /** invite accepted error */
    public const OCM_INVITE_ACCEPTED_ERROR = 'OCM_INVITE_ACCEPTED_ERROR';
    /** the invite cannot be found */
    public const OCM_INVITE_ACCEPTED_NOT_FOUND = 'OCM_INVITE_ACCEPTED_NOT_FOUND';
    /** the invite has been accepted already */
    public const OCM_INVITE_ACCEPTED_EXISTS = 'OCM_INVITE_ACCEPTED_EXISTS';

    /** there are no providers found to display on the wayf page */
    public const WAYF_NO_PROVIDERS_FOUND = 'WAYF_NO_PROVIDERS_FOUND';
    /** an unspecified error occurred trying to build the wayf page */
    public const WAYF_ERROR = 'WAYF_ERROR';
}
