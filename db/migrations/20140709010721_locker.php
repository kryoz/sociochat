<?php

use Phinx\Migration\AbstractMigration;

class Locker extends AbstractMigration
{

    public function change()
    {
	    $table = $this->table('locker', ['id' => true, 'primary_key' => ['id']]);
	    $table
		    ->addColumn('uid', 'string', ['limit' => 64, 'default' => false])
		    ->addColumn('timestamp', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
		    ->addIndex(['uid'])
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