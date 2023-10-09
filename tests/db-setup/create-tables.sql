CREATE TABLE `mesh_invitations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `cloud_id` varchar(255) COLLATE 'utf8mb4_bin' NOT NULL,
  `email` varchar(255) COLLATE 'utf8mb4_bin' NOT NULL
) ENGINE='InnoDB' COLLATE 'utf8mb4_bin';
