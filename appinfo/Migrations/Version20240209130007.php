<?php

namespace OCA\Collaboration\Migrations;

use OCP\IDBConnection;
use OCP\Migration\ISqlMigration;

/**
 * Adds constants to oc_collaboration_constants table
 * Adds views: invitation_view_invitations, invitation_view_remote_users
 */
class Version20240209130007 implements ISqlMigration
{
    public function sql(IDBConnection $connection)
    {
        $stmnt = $connection->prepare(
            "INSERT INTO `oc_collaboration_constants` (`name`, `value`)
		SELECT * FROM (SELECT 'invitation.received', 'received') AS tmp
		WHERE NOT EXISTS (
			SELECT name FROM oc_collaboration_constants WHERE name = 'invitation.received'
		) LIMIT 1;
		INSERT INTO `oc_collaboration_constants` (`name`, `value`)
		SELECT * FROM (SELECT 'invitation.sent', 'sent') AS tmp
		WHERE NOT EXISTS (
			SELECT name FROM oc_collaboration_constants WHERE name = 'invitation.sent'
		) LIMIT 1;

		drop view if exists collaboration_view_invitations;
		create view collaboration_view_invitations as
		select distinct 
		  s.id, s.token, s.timestamp, s.status,
		  s.user_cloud_id, s.user_provider_endpoint, s.sent_received,
		  s.provider_endpoint, s.recipient_endpoint, 
		  s.sender_cloud_id, s.sender_name, s.sender_email, 
		  s.recipient_cloud_id, s.recipient_name, s.recipient_email,
		  s.remote_user_cloud_id, s.remote_user_name, s.remote_user_email, s.remote_user_provider_endpoint as remote_user_provider_endpoint, COALESCE(isp.name, '') as remote_user_provider_name
		 from (
		  select 
		   i.id as id, i.token as token, i.timestamp as timestamp, i.status as status, 
		   i.sender_cloud_id as user_cloud_id, i.provider_endpoint as user_provider_endpoint, (select value from oc_collaboration_constants where name='invitation.sent') as sent_received,
		   i.provider_endpoint as provider_endpoint, i.recipient_endpoint as recipient_endpoint, 
		   i.sender_cloud_id as sender_cloud_id, i.sender_name as sender_name, i.sender_email as sender_email, 
		   i.recipient_cloud_id as recipient_cloud_id, i.recipient_name as recipient_name, i.recipient_email as recipient_email,
		   i.recipient_cloud_id as remote_user_cloud_id, i.recipient_name as remote_user_name, i.recipient_email as remote_user_email, i.recipient_endpoint as remote_user_provider_endpoint
		  from oc_collaboration_invitations i
			union all
		  select 
		   ii.id as id, ii.token as token, ii.timestamp as timestamp, ii.status as status, 
		   ii.recipient_cloud_id as user_cloud_id, ii.recipient_endpoint as user_provider_endpoint, (select value from oc_collaboration_constants where name='invitation.received') as sent_received,
		   ii.provider_endpoint as provider_endpoint, ii.recipient_endpoint as recipient_endpoint, 
		   ii.sender_cloud_id as sender_cloud_id, ii.sender_name as sender_name, ii.sender_email as sender_email, 
		   ii.recipient_cloud_id as recipient_cloud_id, ii.recipient_name as recipient_name, ii.recipient_email as recipient_email,
		   ii.sender_cloud_id as remote_user_cloud_id, ii.sender_name as remote_user_name, ii.sender_email as remote_user_email, ii.provider_endpoint as remote_user_provider_endpoint
		  from oc_collaboration_invitations ii
		 ) s
		left join oc_collaboration_invitation_service_providers as isp
		 on isp.endpoint=s.remote_user_provider_endpoint
		join oc_appconfig c
		 on c.configvalue=s.user_provider_endpoint
		where c.appid='collaboration' and c.configkey='endpoint'
		group by s.id;

		drop view if exists collaboration_view_remote_users;
		create view collaboration_view_remote_users as
		select distinct 
		   s.invitation_id, s.user_cloud_id, s.user_name, s.remote_user_cloud_id, s.remote_user_name, s.remote_user_email, s.remote_provider_endpoint as remote_user_provider_endpoint, isp.name as remote_user_provider_name
		 from (
		  select 
			 i.id as invitation_id, i.provider_endpoint as provider_endpoint, 
			 i.sender_cloud_id as user_cloud_id, i.sender_name as user_name, 
			 i.recipient_cloud_id as remote_user_cloud_id, i.recipient_name as remote_user_name, i.recipient_email as remote_user_email, i.recipient_endpoint as remote_provider_endpoint
		  from oc_collaboration_invitations i
			where i.status='accepted'
		   union all
		  select 
			 ii.id as invitation_id, ii.recipient_endpoint as provider_endpoint, 
			 ii.recipient_cloud_id as user_cloud_id, ii.recipient_name as user_name, 
			 ii.sender_cloud_id as remote_user_cloud_id, ii.sender_name as remote_user_name, ii.sender_email as remote_user_email, ii.provider_endpoint as remote_provider_endpoint
		  from oc_collaboration_invitations ii
			where ii.status='accepted'
		 ) s
		join oc_collaboration_invitation_service_providers as isp
		 on isp.endpoint=s.remote_provider_endpoint
		join oc_appconfig c
		 on c.configvalue=s.provider_endpoint
		 where c.appid='collaboration' and c.configkey='endpoint'
		group by s.invitation_id;"
        );
        $stmnt->execute();
    }
}
