<?php

use Phinx\Migration\AbstractMigration;

class ExtendUser extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('users');
        $table
            ->addColumn('imprint', 'string', ['default' => null, 'null' => true, 'limit' => 32])
            ->addColumn('is_banned', 'boolean', ['default' => false, 'null' => false])
            ->addIndex(['imprint'])
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