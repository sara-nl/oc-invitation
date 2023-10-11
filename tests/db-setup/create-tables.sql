/* TODO: add indices
   TODO: what to do with the prefic 'oc_' ?
 */

DROP TABLE IF EXISTS `oc_mesh_invitations`;
CREATE TABLE `oc_mesh_invitations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `token` varchar(255) COLLATE 'utf8mb4_bin' NOT NULL DEFAULT '',
  `provider_domain` varchar(255) COLLATE 'utf8mb4_bin' NOT NULL DEFAULT '',
  `recipient_domain` varchar(255) COLLATE 'utf8mb4_bin' NOT NULL DEFAULT '',
  `sender_cloud_id` varchar(255) COLLATE 'utf8mb4_bin' DEFAULT "",
  `recipient_cloud_id` varchar(255) COLLATE 'utf8mb4_bin' DEFAULT "",
  `timestamp` int(11) NOT NULL DEFAULT 0,
  `status` varchar(255) COLLATE 'utf8mb4_bin' NOT NULL DEFAULT ''
) ENGINE='InnoDB' COLLATE 'utf8mb4_bin';
