<?php

/**
 * Database schema table names.
 */

namespace OCA\RDMesh\Db;

class Schema
{
    public const id = 'id';
    /* Invitation table name */
    public const Table_Invitations = 'mesh_invitations';
    
    /* Invitation table fields */
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
}
