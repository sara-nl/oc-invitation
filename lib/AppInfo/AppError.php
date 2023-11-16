<?php

/**
 * Translatable errors that may be returned or used as exception messages.
 */

namespace OCA\Invitation\AppInfo;

class AppError
{
    /* unspecified error */
    public const ERROR = 'ERROR';

    /* route errors */
    /* the request is missing a required parameter */
    public const REQUEST_MISSING_PARAMETER = 'REQUEST_MISSING_PARAMETER';

    /* the invitation cannot be found */
    public const INVITATION_NOT_FOUND = 'INVITATION_NOT_FOUND';

    /* create invitation errors */
    /* unspecified create invitation error */
    public const CREATE_INVITATION_ERROR = 'CREATE_INVITATION_ERROR';
    /* the recipient's mail is required */
    public const CREATE_INVITATION_NO_RECIPIENT_EMAIL = 'CREATE_INVITATION_NO_RECIPIENT_EMAIL';
    /* a sender name is required */
    public const CREATE_INVITATION_NO_SENDER_NAME = 'CREATE_INVITATION_NO_SENDER_NAME';
    /* an invitation already exists */
    public const CREATE_INVITATION_EXISTS = 'CREATE_INVITATION_EXISTS';

    /* handle recieved invitation errors */
    /* unspecified handle invitation error */
    public const HANDLE_INVITATION_ERROR = 'HANDLE_INVITATION_ERROR';
    /* an invitation already exists */
    public const HANDLE_INVITATION_EXISTS = 'HANDLE_INVITATION_EXISTS';
    /* invitation is missing the token */
    public const HANDLE_INVITATION_MISSING_TOKEN = 'HANDLE_INVITATION_MISSING_TOKEN';
    /* invitation is missing the provider domain */
    public const HANDLE_INVITATION_MISSING_PROVIDER_DOMAIN = 'HANDLE_INVITATION_MISSING_PROVIDER_DOMAIN';
    /* invitation is missing the sender name */
    public const HANDLE_INVITATION_MISSING_SENDER_NAME = 'HANDLE_INVITATION_MISSING_SENDER_NAME';
    /* The domain provider of the invitation is unknown */
    public const HANDLE_INVITATION_PROVIDER_UNKNOWN = 'HANDLE_INVITATION_PROVIDER_UNKNOWN';

    /* unspecified error retrieving all providers */
    public const MESH_REGISTRY_ALL_PROVIDERS_ERROR = 'MESH_REGISTRY_ALL_PROVIDERS_ERROR';

    /* ocm invite accepted errors */
    /* ocm invite accepted unspecified error */
    public const OCM_INVITE_ACCEPTED_ERROR = 'OCM_INVITE_ACCEPTED_ERROR';
    /* the invite cannot be found */
    public const OCM_INVITE_ACCEPTED_NOT_FOUND = 'OCM_INVITE_ACCEPTED_NOT_FOUND';
    /* the invite has been accepted already */
    public const OCM_INVITE_ACCEPTED_EXISTS = 'OCM_INVITE_ACCEPTED_EXISTS';
}
