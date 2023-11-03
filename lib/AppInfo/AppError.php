<?php

/**
 * App response errors.
 * To be used as translatable error messages. 
 */

namespace OCA\RDMesh\AppInfo;

class AppError
{
    /* unspecified error */
    public const ERROR = 'ERROR';

    /* route errors */
    public const REQUEST_MISSING_PARAMETER = 'REQUEST_MISSING_PARAMETER';

    public const INVITATION_NOT_FOUND = 'INVITATION_NOT_FOUND';

    /* creating invitation errors */
    public const CREATE_INVITATION_ERROR = 'CREATE_INVITATION_ERROR';
    public const CREATE_INVITATION_NO_RECIPIENT_EMAIL = 'CREATE_INVITATION_NO_RECIPIENT_EMAIL';
    public const CREATE_INVITATION_NO_SENDER_NAME = 'CREATE_INVITATION_NO_SENDER_NAME';
    public const CREATE_INVITATION_EXISTS = 'CREATE_INVITATION_EXISTS';

    /* handling recieved invitation errors */
    public const HANDLE_INVITATION_ERROR = 'HANDLE_INVITATION_ERROR';
    public const HANDLE_INVITATION_EXISTS = 'HANDLE_INVITATION_EXISTS';

    /*  */
    public const MESH_REGISTRY_ALL_PROVIDERS_ERROR = 'MESH_REGISTRY_ALL_PROVIDERS_ERROR';

    /* ocm invite accepted errors */
    public const OCM_INVITE_ACCEPTED_NOT_FOUND = 'OCM_INVITE_ACCEPTED_NOT_FOUND';
}
