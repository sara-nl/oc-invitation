<?php

namespace OCA\Invitation\Migration;

use Doctrine\DBAL\Schema\Schema;
use OCP\Migration\ISchemaMigration;

class Version0001Date20231009154348 implements ISchemaMigration
{
    private string $prefix;

    public function changeSchema(Schema $schema, array $options)
    {
        $this->prefix = $options['mesh'];
        if (!$schema->hasTable("{$this->prefix}invitations")) {
            $table = $schema->createTable("{$this->prefix}invitations");
            $table->addColumn('id', 'integer', [
                'autoincrement' => true,
                'unsigned' => true,
                'notnull' => true,
                'length' => 11,
            ]);
            $table->addColumn('cloud_id', 'string', [
                'length' => 255,
                'notnull' => true,
            ]);
            $table->addColumn('email', 'string', [
                'length' => 255,
                'notnull' => true
            ]);
            $table->setPrimaryKey(['id']);
            $table->addUniqueIndex(['cloud_id'], 'cloud_id_index');
        }
    }
}
