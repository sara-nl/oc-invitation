<?php

namespace OCA\invitation\Migrations;

use Doctrine\DBAL\Schema\Schema;
use OCP\Migration\ISchemaMigration;

/**
 * Auto-generated migration step: Please modify to your needs!
 */
class Version20231123130117 implements ISchemaMigration
{
    private string $prefix;

    public function changeSchema(Schema $schema, array $options)
    {
        $this->prefix = $options['tablePrefix'];
        if (!$schema->hasTable("{$this->prefix}test")) {
            $table = $schema->createTable("{$this->prefix}test");
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
