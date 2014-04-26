<?php

use Phinx\Migration\AbstractMigration;

class Init extends AbstractMigration
{

    public function change()
    {
	    $table = $this->table('users', ['id' => true, 'primary_key' => ['id']]);
	    $table->addColumn('social_token', 'string', ['limit' => 128, 'default' => null, 'null' => true])
		    ->addColumn('email', 'string', ['limit' => 50, 'default' => null, 'null' => true])
		    ->addColumn('password', 'string', ['limit' => 60, 'default' => null, 'null' => true])
		    ->addColumn('date_register', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
	        ->addColumn('chat_id', 'string', ['limit' => 32, 'default' => 1])
		    ->addIndex(['email'])
	        ->addIndex(['social_token'])
		    ->save();

	    $table = $this->table('sessions', ['id' => true, 'primary_key' => ['id']]);
	    $table->addColumn('session_id', 'string', ['limit' => 32])
		    ->addColumn('access', 'timestamp')
		    ->addColumn('user_id', 'integer')
		    ->addIndex(['session_id'], ['unique' => true])
		    ->addForeignKey('user_id', 'users', 'id', ['delete'=> 'CASCADE'])
		    ->save();

	    $table = $this->table('user_properties', ['id' => true, 'primary_key' => ['id']]);
	    $table->addColumn('user_id', 'integer')
		    ->addColumn('name', 'string', ['limit' => 20])
		    ->addColumn('sex', 'integer')
		    ->addColumn('tim', 'integer')
		    ->addColumn('notifications', 'text')
		    ->addIndex(['name'], ['unique' => true])
		    ->addForeignKey('user_id', 'users', 'id', ['delete'=> 'CASCADE'])
		    ->save();

	    $table = $this->table('user_blacklist', ['id' => true, 'primary_key' => ['id']]);
	    $table->addColumn('user_id', 'integer')
		    ->addColumn('ignored_user_id', 'integer')
		    ->addForeignKey('ignored_user_id', 'users', 'id', ['delete'=> 'CASCADE'])
		    ->addForeignKey('user_id', 'users', 'id', ['delete'=> 'CASCADE'])
		    ->save();

	    $table = $this->table('activations', ['id' => true, 'primary_key' => ['id']]);
	    $table->addColumn('email', 'string', ['limit' => 50])
		    ->addColumn('code', 'string', ['limit' => 64])
		    ->addColumn('timestamp', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
	        ->addColumn('used', 'boolean', ['default' => false])
		    ->addIndex(['email', 'code', 'timestamp'])
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