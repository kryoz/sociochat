<?php

use Phinx\Migration\AbstractMigration;

class Subscription extends AbstractMigration
{
    public function change()
    {
	    $table = $this->table('user_properties');
	    $table
		    ->addColumn('is_subscribed', 'boolean', ['default' => 'true'])
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