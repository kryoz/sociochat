<?php

use Phinx\Migration\AbstractMigration;

class AddChannels extends AbstractMigration
{

    public function change()
    {
	    $table = $this->table('channels', ['id' => true, 'primary_key' => ['id']]);
	    $table->addColumn('owner_id', 'integer')
		    ->addColumn('name', 'string', ['limit' => 100])
		    ->addColumn('is_private', 'boolean', ['default' => true])
		    ->addForeignKey('owner_id', 'users', 'id', ['delete'=> 'RESTRICT', 'update' => 'CASCADE'])
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