<?php

use Phinx\Migration\AbstractMigration;

class TmpSessions extends AbstractMigration
{

    public function change()
    {
	    $table = $this->table('tmp_sessions', ['id' => true, 'primary_key' => ['id']]);
	    $table->addColumn('session_id', 'string', ['limit' => 32])
		    ->addIndex(['session_id'], ['unique' => true])
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