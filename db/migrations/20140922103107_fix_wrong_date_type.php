<?php

use Phinx\Migration\AbstractMigration;

class FixWrongDateType extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
	    $table = $this->table('user_properties');
	    $table
		    ->changeColumn('birth', 'date', ['default' => '1930-01-01'])
		    ->update();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
	    $table = $this->table('user_properties');
	    $table
		    ->changeColumn('birth', 'timestamp', ['default' => null, 'null' => true])
		    ->update();
    }
}