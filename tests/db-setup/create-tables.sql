/* TODO: add indices
   TODO: should we use prefix 'oc_' or something else ?
 */

DROP TABLE IF EXISTS `oc_mesh_invitations`;
CREATE TABLE `oc_mesh_invitations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `token` varchar(255) COLLATE 'utf8mb4_bin' UNIQUE NOT NULL DEFAULT "",
  `provider_domain` varchar(255) COLLATE 'utf8mb4_bin' NOT NULL DEFAULT '',
  `recipient_domain` varchar(255) COLLATE 'utf8mb4_bin' NOT NULL DEFAULT '',
  `sender_cloud_id` varchar(255) COLLATE 'utf8mb4_bin' DEFAULT "",
  `sender_email` varchar(255) COLLATE 'utf8mb4_bin' DEFAULT "",
  `sender_name` varchar(255) COLLATE 'utf8mb4_bin' DEFAULT "",
  `recipient_cloud_id` varchar(255) COLLATE 'utf8mb4_bin' DEFAULT "",
  `recipient_email` varchar(255) COLLATE 'utf8mb4_bin' DEFAULT "",
  `recipient_name` varchar(255) COLLATE 'utf8mb4_bin' DEFAULT "",
  `timestamp` int(11) NOT NULL DEFAULT 0,
  `status` varchar(255) COLLATE 'utf8mb4_bin' NOT NULL DEFAULT ''
) ENGINE='InnoDB' COLLATE 'utf8mb4_bin';

drop view if exists oc_mesh_view_remote_users;
create view oc_mesh_view_remote_users as
select distinct s.invitation_id, s.user_cloud_id, s.user_name, s.remote_user_cloud_id, s.remote_user_name
 from (
  select i.id as invitation_id, i.provider_domain as provider_domain, i.sender_cloud_id as user_cloud_id, i.sender_name as user_name, i.recipient_cloud_id as remote_user_cloud_id, i.recipient_name as remote_user_name from oc_mesh_invitations i
   union all
  select ii.id as invitation_id, ii.recipient_domain as provider_domain, ii.recipient_cloud_id as user_cloud_id, ii.recipient_name as user_name, ii.sender_cloud_id as remote_user_cloud_id, ii.sender_name as remote_user_name from oc_mesh_invitations ii
 ) s
join oc_appconfig c
 on c.configvalue=s.provider_domain
 where c.appid='rd-mesh' and c.configkey='domain'
group by s.invitation_id;