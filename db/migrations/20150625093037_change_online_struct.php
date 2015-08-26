<?php

use Phinx\Migration\AbstractMigration;

class ChangeOnlineStruct extends AbstractMigration
{

    public function change()
    {
	    $table = $this->table('users_online');
	    $table
		    ->drop();
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