<?php

use Phinx\Migration\AbstractMigration;

class CreateNotes extends AbstractMigration
{

    public function change()
    {
        $table = $this->table('user_notes', ['id' => true, 'primary_key' => ['id']]);
        $table
            ->addColumn('user_id', 'integer')
            ->addColumn('noted_user_id', 'integer')
            ->addColumn('note', 'string', ['limit' => 255])
            ->addForeignKey('noted_user_id', 'users', 'id', ['delete' => 'CASCADE'])
            ->addForeignKey('user_id', 'users', 'id', ['delete' => 'CASCADE'])
            ->save();
    }

    /**
     * Migrate Up.
     */
    public function up()
    {

    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}