<?php

use Phinx\Migration\AbstractMigration;

class OnlineUsers extends AbstractMigration
{
    public function change()
    {
	    $table = $this->table('users_online', ['id' => true, 'primary_key' => ['id']]);
	    $table
		    ->addColumn('user_id', 'integer')
		    ->addForeignKey('user_id', 'users', 'id', ['delete' => 'CASCADE'])
		    ->save();
    }
}