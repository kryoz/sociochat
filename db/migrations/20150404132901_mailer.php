<?php

use Phinx\Migration\AbstractMigration;

class Mailer extends AbstractMigration
{

    public function change()
    {
	    $table = $this->table('mail_queue', ['id' => true, 'primary_key' => ['id']]);
	    $table
		    ->addColumn('email', 'string', ['limit' => 50])
		    ->addColumn('topic', 'string', ['limit' => 100])
		    ->addColumn('message', 'string', ['limit' => 8192])
		    ->addColumn('date_register', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
		    ->save();
    }
}