<?php

namespace OCA\RDMesh\Federation;

use OCA\RDMesh\Db\Schema;
use OCA\RDMesh\Federation\Invitation;
use OCP\AppFramework\Db\Mapper;
use OCP\IDb;

class InvitationMapper extends Mapper
{
    public function __construct(IDb $db)
    {
        parent::__construct($db, Schema::Table_Invitations, Invitation::class);
    }

    // public function find(string $id)
    // {
    //     $sql = 'SELECT * FROM *PREFIX*ownnotes_notes WHERE id = ? AND user_id = ?';
    //     return $this->findEntity($sql, [$id]);
    // }
}
