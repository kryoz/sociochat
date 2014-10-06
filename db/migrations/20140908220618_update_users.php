<?php

use Phinx\Migration\AbstractMigration;

class UpdateUsers extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('users');
        $table
            ->addColumn('messages_count', 'integer', ['default' => 0, 'null' => false])
            ->addColumn('role', 'integer', ['default' => 1, 'null' => false])
            ->removeColumn('social_token')
            ->update();
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