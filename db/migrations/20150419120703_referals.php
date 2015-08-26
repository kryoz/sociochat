<?php

use Phinx\Migration\AbstractMigration;

class Referals extends AbstractMigration
{
    public function change()
    {
	    $table = $this->table('users_ref', ['id' => true, 'primary_key' => ['id']]);
	    $table
		    ->addColumn('user_id', 'integer')
		    ->addColumn('ref_user_id', 'integer')
		    ->addColumn('date_register', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
		    ->addForeignKey('user_id', 'users', 'id', ['delete' => 'CASCADE'])
		    ->addForeignKey('ref_user_id', 'users', 'id', ['delete' => 'CASCADE'])
		    ->save();
    }
}