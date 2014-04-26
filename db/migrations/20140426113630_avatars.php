<?php

use Phinx\Migration\AbstractMigration;

class Avatars extends AbstractMigration
{

    public function change()
    {
	    $table = $this->table('user_properties');
	    $table->addColumn('avatar_url', 'string', ['limit' => 255, 'default' => 'null'])
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