<?php

use Phinx\Migration\AbstractMigration;

class MakeUniqIndexOnLocker extends AbstractMigration
{

    public function change()
    {
	    $table = $this->table('locker');
	    $table
		    ->removeIndex('uid')
		    ->addIndex(['uid'], ['unique' => true])
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