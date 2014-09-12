<?php

use Phinx\Migration\AbstractMigration;

class NewUserProperties extends AbstractMigration
{
	public function change()
	{
		$table = $this->table('user_properties');
		$table
			->addColumn('city', 'string', ['limit' => 50, 'default' => null, 'null' => true])
			->addColumn('birth', 'timestamp', ['default' => null, 'null' => true])
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