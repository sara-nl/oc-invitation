<?php

namespace OCA\Collaboration\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\SchemaException;
use Doctrine\DBAL\Types\Types;
use Exception;
use OCP\Migration\ISchemaMigration;

/**
 * Auto-generated migration step: Please modify to your needs!
 */
class Version20231130125300 implements ISchemaMigration
{
  /**
   *
   */
    public function changeSchema(Schema $schema, array $options)
    {
        $prefix = $options['tablePrefix'];

      //----------------------
      // The collaboration constants table
      //----------------------
        try {
            $schema->getTable("{$prefix}collaboration_constants");
        } catch (Exception $e) {
            if ($e->getCode() == SchemaException::TABLE_DOESNT_EXIST) {
                $table = $schema->createTable("{$prefix}collaboration_constants");
                $table->addColumn('name', Types::STRING, [
                'length' => 255,
                'notnull' => true,
                ]);
                $table->addColumn('value', Types::STRING, [
                'length' => 255,
                'notnull' => true,
                ]);
                $table->setPrimaryKey(['name']);
            } else {
                throw new Exception($e->getMessage(), $e->getCode());
            }
        }
    }
}
