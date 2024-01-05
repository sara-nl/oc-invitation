<?php

namespace OCA\invitation\Migrations;

use OCP\IDBConnection;
use OCP\Migration\ISqlMigration;

/**
 * This step inserts all data into the oc-1 database required for the integration tests.
 */
class Version20231130125301 implements ISqlMigration
{
    public function sql(IDBConnection $connection)
    {
        $stmt = $connection->prepare("
        INSERT INTO `oc_appconfig` (`appid`, `configkey`, `configvalue`) VALUES
        ('invitation',	'allow_sharing_with_invited_users_only', 'yes'),
        ('invitation',	'endpoint',	'https://oc-1.nl/apps/invitation'),
        ('invitation',	'types',	'filesystem')
        ON DUPLICATE KEY UPDATE `appid` = VALUES(`appid`), `configkey` = VALUES(`configkey`), `configvalue` = VALUES(`configvalue`);
        
        DELETE FROM `oc_invitation_invitation_service_providers`;
        INSERT INTO `oc_invitation_invitation_service_providers` (`domain`, `endpoint`, `name`) VALUES
        ('oc-1.nl',	'https://oc-1.nl/apps/invitation', 'OC 1 University')
        ON DUPLICATE KEY UPDATE `id` = VALUES(`id`), `domain` = VALUES(`domain`), `endpoint` = VALUES(`endpoint`), `name` = VALUES(`name`);
        
        DELETE FROM `oc_migrations`
        WHERE `app`='invitation';
        ");

        $stmt->execute();
    }
}
