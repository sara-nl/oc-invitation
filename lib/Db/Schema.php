<?php

/**
 * Database schema table names.
 */

namespace OCA\RDMesh\Db;

class Schema
{
    public const id = 'id';

    /* Invitations table */
    public const Table_Invitations = 'mesh_invitations';

    public const Invitation_token = 'token';
    public const Invitation_provider_domain = 'provider_domain';
    public const Invitation_recipient_domain = 'recipient_domain';
    public const Invitation_sender_cloud_id = 'sender_cloud_id';
    public const Invitation_sender_email = 'sender_email';
    public const Invitation_sender_name = 'sender_name';
    public const Invitation_recipient_cloud_id = 'recipient_cloud_id';
    public const Invitation_recipient_email = 'recipient_email';
    public const Invitation_recipient_name = 'recipient_name';
    public const Invitation_timestamp = 'timestamp';
    public const Invitation_status = 'status';

    /* Invitations view */
    public const View_Invitations = 'mesh_view_invitations';

    public const VInvitation_token = 'token';
    public const VInvitation_timestamp = 'timestamp';
    public const VInvitation_status = 'status';
    public const VInvitation_user_cloud_id = 'user_cloud_id';
    public const VInvitation_sent_received = 'sent_received';
    public const VInvitation_provider_domain = 'provider_domain';
    public const VInvitation_recipient_domain = 'recipient_domain';
    public const VInvitation_sender_cloud_id = 'sender_cloud_id';
    public const VInvitation_sender_email = 'sender_email';
    public const VInvitation_sender_name = 'sender_name';
    public const VInvitation_recipient_cloud_id = 'recipient_cloud_id';
    public const VInvitation_recipient_email = 'recipient_email';
    public const VInvitation_recipient_name = 'recipient_name';
    public const VInvitation_remote_user_name = 'remote_user_name';
    public const VInvitation_remote_user_cloud_id = 'remote_user_cloud_id';
    public const VInvitation_remote_user_email = 'remote_user_email';

    /* Remote Users view */
    public const View_RemoteUsers = 'mesh_view_remote_users';

    public const RemoteUser_invitation_id = 'invitation_id';
    public const RemoteUser_user_cloud_id = 'user_cloud_id';
    public const RemoteUser_user_name = 'user_name';
    public const RemoteUser_remote_user_cloud_id = 'remote_user_cloud_id';
    public const RemoteUser_remote_user_name = 'remote_user_name';
}
