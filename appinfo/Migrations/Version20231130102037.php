<?php

namespace OCA\invitation\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use OCP\Migration\ISchemaMigration;

/**
 * Auto-generated migration step: Please modify to your needs!
 */
class Version20231130102037 implements ISchemaMigration
{
    /**
     *
     */
    public function changeSchema(Schema $schema, array $options)
    {
        $prefix = $options['tablePrefix'];

        // The invitations table
        $table = $schema->createTable("{$prefix}mesh_invitations");
        $table->addColumn('id', Types::BIGINT, [
            'autoincrement' => true,
            'unsigned' => true,
            'notnull' => true,
            'length' => 20,
        ]);
        $table->addColumn('user_cloud_id', Types::STRING, [
            'length' => 255,
            'notnull' => true,
            'default' => '',
        ]);
        $table->addColumn('token', Types::STRING, [
            'length' => 255,
            'notnull' => true,
            'default' => '',
        ]);
        $table->addColumn('provider_domain', Types::STRING, [
            'length' => 255,
            'notnull' => true,
            'default' => '',
        ]);
        $table->addColumn('recipient_domain', Types::STRING, [
            'length' => 255,
            'notnull' => true,
            'default' => '',
        ]);
        $table->addColumn('sender_cloud_id', Types::STRING, [
            'length' => 255,
            'notnull' => true,
            'default' => '',
        ]);
        $table->addColumn('sender_email', Types::STRING, [
            'length' => 255,
            'notnull' => true,
            'default' => '',
        ]);
        $table->addColumn('sender_name', Types::STRING, [
            'length' => 255,
            'notnull' => true,
            'default' => '',
        ]);
        $table->addColumn('recipient_cloud_id', Types::STRING, [
            'length' => 255,
            'notnull' => true,
            'default' => '',
        ]);
        $table->addColumn('recipient_email', Types::STRING, [
            'length' => 255,
            'notnull' => true,
            'default' => '',
        ]);
        $table->addColumn('recipient_name', Types::STRING, [
            'length' => 255,
            'notnull' => true,
            'default' => '',
        ]);
        $table->addColumn('timestamp', Types::INTEGER, [
            'length' => 11,
            'notnull' => true,
            'default' => 0,
        ]);
        $table->addColumn('status', Types::STRING, [
            'length' => 255,
            'notnull' => true,
            'default' => '',
        ]);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['user_cloud_id'], 'user_cloud_id_index');

        // the domain_providers table
        $table = $schema->createTable("{$prefix}mesh_domain_providers");
        $table->addColumn('id', Types::BIGINT, [
            'autoincrement' => true,
            'unsigned' => true,
            'notnull' => true,
            'length' => 20,
        ]);
        $table->addColumn('domain', Types::STRING, [
            'length' => 255,
            'notnull' => true,
            'default' => '',
        ]);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['domain'], 'domain_index');
    }
}
