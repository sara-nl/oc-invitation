<?php

/**
 * App error codes
 */

namespace OCA\RDMesh\AppInfo;

class AppError
{
    /* Http response error codes */
    public const ERROR = 'ERROR';

    public const CREATE_INVITATION_ERROR = 'CREATE_INVITATION_ERROR';
    public const CREATE_INVITATION_NO_EMAIL = 'CREATE_INVITATION_NO_EMAIL';
    public const CREATE_INVITATION_NO_NAME = 'CREATE_INVITATION_NO_NAME';
    public const CREATE_INVITATION_EXISTS = 'CREATE_INVITATION_EXISTS';
    public const OCM_INVITE_ACCEPTED_NOT_FOUND = 'OCM_INVITE_ACCEPTED_NOT_FOUND';
}
