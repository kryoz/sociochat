<?php

use Phinx\Migration\AbstractMigration;

class NameChangeLog extends AbstractMigration
{

    public function change()
    {
	    $table = $this->table('name_change_history', ['id' => true, 'primary_key' => ['id']]);
	    $table->addColumn('user_id', 'integer')
		    ->addColumn('old_name', 'string', ['limit' => 20])
		    ->addColumn('date_change', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
		    ->addForeignKey('user_id', 'users', 'id', ['delete'=> 'CASCADE', 'update' => 'CASCADE'])
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