/* TODO: add indices
   TODO: is prefix 'oc_' ok ?
 */

DROP TABLE IF EXISTS `oc_invitation_invitations`;
CREATE TABLE `oc_invitation_invitations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `user_cloud_id` varchar(255) COLLATE 'utf8mb4_bin' DEFAULT "",
  `token` varchar(255) COLLATE 'utf8mb4_bin' UNIQUE NOT NULL DEFAULT "",
  `provider_endpoint` varchar(255) COLLATE 'utf8mb4_bin' NOT NULL DEFAULT '',
  `recipient_endpoint` varchar(255) COLLATE 'utf8mb4_bin' NOT NULL DEFAULT '',
  `sender_cloud_id` varchar(255) COLLATE 'utf8mb4_bin' DEFAULT "",
  `sender_email` varchar(255) COLLATE 'utf8mb4_bin' DEFAULT "",
  `sender_name` varchar(255) COLLATE 'utf8mb4_bin' DEFAULT "",
  `recipient_cloud_id` varchar(255) COLLATE 'utf8mb4_bin' DEFAULT "",
  `recipient_email` varchar(255) COLLATE 'utf8mb4_bin' DEFAULT "",
  `recipient_name` varchar(255) COLLATE 'utf8mb4_bin' DEFAULT "",
  `timestamp` int(11) NOT NULL DEFAULT 0,
  `status` varchar(255) COLLATE 'utf8mb4_bin' NOT NULL DEFAULT ''
) ENGINE='InnoDB' COLLATE 'utf8mb4_bin';

DROP TABLE IF EXISTS `oc_invitation_invitation_service_providers`;
CREATE TABLE `oc_invitation_invitation_service_providers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `domain` varchar(255) COLLATE 'utf8mb4_bin' UNIQUE NOT NULL DEFAULT "",
  `endpoint` varchar(255) COLLATE 'utf8mb4_bin' UNIQUE NOT NULL DEFAULT "",
  `name` varchar(255) COLLATE 'utf8mb4_bin' UNIQUE NOT NULL DEFAULT ""
) ENGINE='InnoDB' COLLATE 'utf8mb4_bin';

drop view if exists oc_invitation_view_invitations;
create view oc_invitation_view_invitations as
select distinct 
  s.id, s.token, s.timestamp, s.status,
  s.user_cloud_id, s.user_provider_endpoint, s.sent_received,
  s.provider_endpoint, s.recipient_endpoint, 
  s.sender_cloud_id, s.sender_name, s.sender_email, 
  s.recipient_cloud_id, s.recipient_name, s.recipient_email,
  s.remote_user_cloud_id, s.remote_user_name, s.remote_user_email
 from (
  select 
   i.id as id, i.token as token, i.timestamp as timestamp, i.status as status, 
   i.sender_cloud_id as user_cloud_id, i.provider_endpoint as user_provider_endpoint, 'sent' COLLATE utf8mb4_bin as sent_received,
   i.provider_endpoint as provider_endpoint, i.recipient_endpoint as recipient_endpoint, 
   i.sender_cloud_id as sender_cloud_id, i.sender_name as sender_name, i.sender_email as sender_email, 
   i.recipient_cloud_id as recipient_cloud_id, i.recipient_name as recipient_name, i.recipient_email as recipient_email,
   i.recipient_cloud_id as remote_user_cloud_id, i.recipient_name as remote_user_name, i.recipient_email as remote_user_email
  from oc_invitation_invitations i
    union all
  select 
   ii.id as id, ii.token as token, ii.timestamp as timestamp, ii.status as status, 
   ii.recipient_cloud_id as user_cloud_id, ii.recipient_endpoint as user_provider_endpoint, 'received' COLLATE utf8mb4_bin as sent_received,
   ii.provider_endpoint as provider_endpoint, ii.recipient_endpoint as recipient_endpoint, 
   ii.sender_cloud_id as sender_cloud_id, ii.sender_name as sender_name, ii.sender_email as sender_email, 
   ii.recipient_cloud_id as recipient_cloud_id, ii.recipient_name as recipient_name, ii.recipient_email as recipient_email,
   ii.sender_cloud_id as remote_user_cloud_id, ii.sender_name as remote_user_name, ii.sender_email as remote_user_email
  from oc_invitation_invitations ii
 ) s
join oc_appconfig c
 on c.configvalue=s.user_provider_endpoint
where c.appid='invitation' and c.configkey='endpoint'
group by s.id;

drop view if exists oc_invitation_view_remote_users;
create view oc_invitation_view_remote_users as
select distinct 
   s.invitation_id, s.user_cloud_id, s.user_name, s.remote_user_cloud_id, s.remote_user_name
 from (
  select 
     i.id as invitation_id, i.provider_endpoint as provider_endpoint, 
     i.sender_cloud_id as user_cloud_id, i.sender_name as user_name, 
     i.recipient_cloud_id as remote_user_cloud_id, i.recipient_name as remote_user_name 
  from oc_invitation_invitations i
    where i.status='accepted'
   union all
  select 
     ii.id as invitation_id, ii.recipient_endpoint as provider_endpoint, 
     ii.recipient_cloud_id as user_cloud_id, ii.recipient_name as user_name, 
     ii.sender_cloud_id as remote_user_cloud_id, ii.sender_name as remote_user_name 
  from oc_invitation_invitations ii
    where ii.status='accepted'
 ) s
join oc_appconfig c
 on c.configvalue=s.provider_endpoint
 where c.appid='invitation' and c.configkey='endpoint'
group by s.invitation_id;