<?php

namespace OCA\invitation\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\SchemaException;
use Doctrine\DBAL\Types\Types;
use Exception;
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

        try {
            $schema->getTable("{$prefix}invitation_invitations");
        } catch (Exception $e) {
            if ($e->getCode() == SchemaException::TABLE_DOESNT_EXIST) {
                //----------------------
                // The invitations table
                //----------------------
                $table = $schema->createTable("{$prefix}invitation_invitations");
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
                $table->addColumn('provider_endpoint', Types::STRING, [
                    'length' => 255,
                    'notnull' => true,
                    'default' => '',
                ]);
                $table->addColumn('recipient_endpoint', Types::STRING, [
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
            } else {
                throw new Exception($e->getMessage(), $e->getCode());
            }
        }
        try {
            $schema->getTable("{$prefix}invitation_invitation_service_providers");
        } catch (Exception $e) {
            if ($e->getCode() == SchemaException::TABLE_DOESNT_EXIST) {
                //---------------------------------------
                // the invitation_service_providers table
                //---------------------------------------
                $table = $schema->createTable("{$prefix}invitation_invitation_service_providers");
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
                // the endpoint of this invitation service provider
                $table->addColumn('endpoint', Types::STRING, [
                    'length' => 255,
                    'notnull' => true,
                    'default' => '',
                ]);
                // the endpoint of this invitation service provider
                $table->addColumn('name', Types::STRING, [
                    'length' => 255,
                    'notnull' => true,
                    'default' => '',
                ]);
                $table->setPrimaryKey(['id']);
                $table->addUniqueIndex(['endpoint'], 'endpoint_index');
            } else {
                throw new Exception($e->getMessage(), $e->getCode());
            }
        }
    }
}
