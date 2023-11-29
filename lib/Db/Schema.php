<?php

/**
 * Database schema table names.
 */

namespace OCA\Invitation\Db;

class Schema
{
    public const ID = 'id';

    /* Invitations table */
    public const TABLE_INVITATIONS = 'mesh_invitations';

    public const INVITATION_USER_CLOUD_ID = 'user_cloud_id';
    public const INVITATION_TOKEN = 'token';
    public const INVITATION_PROVIDER_DOMAIN = 'provider_domain';
    public const INVITATION_RECIPIENT_DOMAIN = 'recipient_domain';
    public const INVITATION_SENDER_CLOUD_ID = 'sender_cloud_id';
    public const INVITATION_SENDER_EMAIL = 'sender_email';
    public const INVITATION_SENDER_NAME = 'sender_name';
    public const INVITATION_RECIPIENT_CLOUD_ID = 'recipient_cloud_id';
    public const INVITATION_RECIPIENT_EMAIL = 'recipient_email';
    public const INVITATION_RECIPIENT_NAME = 'recipient_name';
    public const INVITATION_TIMESTAMP = 'timestamp';
    public const INVITATION_STATUS = 'status';

    /* Invitations view */
    public const VIEW_INVITATIONS = 'mesh_view_invitations';

    public const VINVITATION_TOKEN = 'token';
    public const VINVITATION_TIMESTAMP = 'timestamp';
    public const VINVITATION_STATUS = 'status';
    public const VINVITATION_USER_CLOUD_ID = 'user_cloud_id';
    public const VINVITATION_SEND_RECEIVED = 'sent_received';
    public const VINVITATION_PROVIDER_DOMAIN = 'provider_domain';
    public const VINVITATION_RECIPIENT_DOMAIN = 'recipient_domain';
    public const VINVITATION_SENDER_CLOUD_ID = 'sender_cloud_id';
    public const VINVITATION_SENDER_EMAIL = 'sender_email';
    public const VINVITATION_SENDER_NAME = 'sender_name';
    public const VINVITATION_RECIPIENT_CLOUD_ID = 'recipient_cloud_id';
    public const VINVITATION_RECIPIENT_EMAIL = 'recipient_email';
    public const VINVITATION_RECIPIENT_NAME = 'recipient_name';
    public const VINVITATION_REMOTE_USER_NAME = 'remote_user_name';
    public const VINVITATION_REMOTE_USER_CLOUD_ID = 'remote_user_cloud_id';
    public const VINVITATION_REMOTE_USER_EMAIL = 'remote_user_email';

    /* Remote Users view */
    public const VIEW_REMOTEUSERS = 'mesh_view_remote_users';

    public const REMOTEUSER_INVITATION_ID = 'invitation_id';
    public const REMOTEUSER_USER_CLOUD_ID = 'user_cloud_id';
    public const REMOTEUSER_USER_NAME = 'user_name';
    public const REMOTEUSER_REMOTE_USER_CLOUD_ID = 'remote_user_cloud_id';
    public const REMOTEUSER_REMOTE_USER_NAME = 'remote_user_name';

    /* Domain providers table */
    public const TABLE_DOMAINPROVIDERS = 'mesh_domain_providers';

    public const DOMAINPROVIDER_DOMAIN = 'domain';
}
