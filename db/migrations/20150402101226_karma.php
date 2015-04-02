<?php

use Phinx\Migration\AbstractMigration;

class Karma extends AbstractMigration
{
    public function change()
    {
	    $table = $this->table('user_karma', ['id' => true, 'primary_key' => ['id']]);
	    $table
		    ->addColumn('user_id', 'integer')
		    ->addColumn('evaluator_id', 'integer')
		    ->addColumn('mark', 'integer', ['default' => '0'])
		    ->addColumn('date_register', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
		    ->addForeignKey('user_id', 'users', 'id', ['delete' => 'CASCADE'])
		    ->addForeignKey('evaluator_id', 'users', 'id', ['delete' => 'CASCADE'])
		    ->save();
    }
}